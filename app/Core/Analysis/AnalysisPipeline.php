<?php

declare(strict_types=1);

namespace App\Core\Analysis;

use App\Core\Graph\Filter;
use App\Core\Graph\GraphBuilder;
use App\Core\Parser\ClassParser;
use App\Core\Parser\ParsedClass;
use App\Core\Parser\ParseError;
use App\Core\Parser\SourceScanner;
use App\Core\Report\Report;

/**
 * Scans, parses and analyses the sources, then applies the filter to the report entries.
 *
 * Metrics are always computed on the full graph: filtering never changes a value, only what is reported.
 * Class and parse error file paths are made relative to the base path right after parsing.
 */
final readonly class AnalysisPipeline
{
    public function __construct(
        private ClassParser $parser,
    ) {}

    public function run(AnalysisOptions $options): Report
    {
        $files = (new SourceScanner)->scan($options->paths, $options->excludePaths, $options->basePath);

        [$classes, $errors] = $this->parse($files, $options->basePath);

        $graph = (new GraphBuilder)->build($classes);
        $componentAnalyzer = new ComponentAnalyzer(new ComponentResolver($options->components, $options->componentDepth));

        $classMetrics = self::retainClasses((new CouplingAnalyzer)->analyze($graph), $options->filter);
        $componentMetrics = self::retainComponents($componentAnalyzer->analyze($graph), $options->filter);

        return new Report(
            classes: $classMetrics,
            components: $componentMetrics,
            cycles: self::retainCycles((new CycleAnalyzer($componentAnalyzer))->analyze($graph), $options->filter, $componentMetrics),
            sdpViolations: self::retainSdpViolations((new SdpAnalyzer($componentAnalyzer))->analyze($graph), $componentMetrics),
            errors: $errors,
            duplicates: $graph->duplicates(),
            fileCount: count($files),
        );
    }

    /**
     * @param  list<string>  $files
     * @return array{list<ParsedClass>, list<ParseError>}
     */
    private function parse(array $files, string $basePath): array
    {
        $classes = [];
        $errors = [];

        foreach ($files as $file) {
            $result = $this->parser->parse($file);

            array_push($classes, ...$result->classes);
            array_push($errors, ...$result->errors);
        }

        $prefix = rtrim(realpath($basePath) ?: $basePath, '/').'/';

        return [
            array_map(static fn (ParsedClass $class): ParsedClass => new ParsedClass(
                $class->fqcn,
                $class->kind,
                $class->dependencies,
                self::relativePath($class->file, $prefix),
            ), $classes),
            array_map(static fn (ParseError $error): ParseError => new ParseError(
                self::relativePath($error->file, $prefix),
                $error->message,
            ), $errors),
        ];
    }

    private static function relativePath(string $file, string $prefix): string
    {
        return str_starts_with($file, $prefix) ? substr($file, strlen($prefix)) : $file;
    }

    /**
     * @param  array<string, ClassMetrics>  $classes
     * @return array<string, ClassMetrics>
     */
    private static function retainClasses(array $classes, Filter $filter): array
    {
        return array_filter($classes, static fn (ClassMetrics $metrics): bool => $filter->matches($metrics->fqcn));
    }

    /**
     * A component is retained when at least one of its classes is.
     *
     * @param  array<string, ComponentMetrics>  $components
     * @return array<string, ComponentMetrics>
     */
    private static function retainComponents(array $components, Filter $filter): array
    {
        return array_filter(
            $components,
            static fn (ComponentMetrics $metrics): bool => array_filter($metrics->classes, $filter->matches(...)) !== [],
        );
    }

    /**
     * A class cycle is retained when one of its members is, a component cycle when one of its components is.
     *
     * @param  list<Cycle>  $cycles
     * @param  array<string, ComponentMetrics>  $retainedComponents
     * @return list<Cycle>
     */
    private static function retainCycles(array $cycles, Filter $filter, array $retainedComponents): array
    {
        $isRetained = static fn (Cycle $cycle): bool => array_filter(
            $cycle->members,
            static fn (string $member): bool => $cycle->level === CycleLevel::Class_
                ? $filter->matches($member)
                : isset($retainedComponents[$member]),
        ) !== [];

        return array_values(array_filter($cycles, $isRetained));
    }

    /**
     * A violation is retained when its source or target component is.
     *
     * @param  list<SdpViolation>  $violations
     * @param  array<string, ComponentMetrics>  $retainedComponents
     * @return list<SdpViolation>
     */
    private static function retainSdpViolations(array $violations, array $retainedComponents): array
    {
        $isRetained = static fn (SdpViolation $violation): bool => isset($retainedComponents[$violation->from])
            || isset($retainedComponents[$violation->to]);

        return array_values(array_filter($violations, $isRetained));
    }
}
