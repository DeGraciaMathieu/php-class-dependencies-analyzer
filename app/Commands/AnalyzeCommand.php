<?php

namespace App\Commands;

use App\Core\Analysis\AnalysisPipeline;
use App\Core\Config\Config;
use App\Core\Config\ConfigException;
use App\Core\Config\ConfigLoader;
use App\Core\Rendering\JsonRenderer;
use App\Core\Rendering\Renderer;
use App\Core\Rendering\ReportSection;
use App\Core\Rendering\TableRenderer;
use App\Core\Report\Report;
use App\Core\Rules\Rule;
use App\Core\Rules\Violation;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzeCommand extends AbstractCommand
{
    /**
     * Exit codes: 0 (SUCCESS) no rule violated, 1 (FAILURE) at least one rule violated,
     * 2 (ERROR) invalid configuration or option.
     */
    public const ERROR = 2;

    protected $signature = 'analyze
        {paths?* : Files or directories to analyze, replace the configured paths}
        {--config= : Configuration file, defaults to dependencies.php in the current directory}
        {--report= : Comma-separated sections: classes, components, cycles, sdp}
        {--format=table : Output format: table or json}
        {--output= : Write the report to this file instead of the standard output}
        {--only= : Comma-separated FQCN globs to keep, replace the configured "only"}
        {--exclude= : Comma-separated FQCN globs to ignore, replace the configured "ignore"}
    ';

    protected $description = 'Analyze class and component dependencies';

    public function handle(ConfigLoader $loader, AnalysisPipeline $pipeline): int
    {
        try {
            $renderer = $this->makeRenderer();
            $sections = $this->makeSections();
            $config = $this->mergeCliOptions($this->loadConfig($loader));

            $report = $pipeline->run($config->toOptions(getcwd()));
            $violations = $this->evaluateRules($config->rules, $report);

            $this->write($renderer->render($report, $sections, $violations));
        } catch (ConfigException|InvalidArgumentException $e) {
            $this->output->getErrorStyle()->writeln("<error>{$e->getMessage()}</error>");

            return self::ERROR;
        }

        return $violations === null || $violations === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  list<Rule>  $rules
     * @return list<Violation>|null Null when no rule is configured
     */
    private function evaluateRules(array $rules, Report $report): ?array
    {
        if ($rules === []) {
            return null;
        }

        return array_merge(...array_map(static fn (Rule $rule): array => $rule->evaluate($report), $rules));
    }

    private function loadConfig(ConfigLoader $loader): Config
    {
        $file = $this->option('config') ?? $loader->find(getcwd());

        return $file === null ? Config::make() : $loader->load($file);
    }

    private function mergeCliOptions(Config $config): Config
    {
        $paths = $this->argument('paths');

        if ($paths !== []) {
            $config = $config->paths($paths);
        }

        if ($this->option('only') !== null) {
            $config = $config->only($this->optionToList('only'));
        }

        if ($this->option('exclude') !== null) {
            $config = $config->ignore($this->optionToList('exclude'));
        }

        return $config;
    }

    private function makeRenderer(): Renderer
    {
        $format = $this->option('format');

        return match ($format) {
            'table' => new TableRenderer,
            'json' => new JsonRenderer,
            default => throw new InvalidArgumentException("Unknown format \"{$format}\", expected one of: table, json."),
        };
    }

    /**
     * @return list<ReportSection>
     */
    private function makeSections(): array
    {
        if ($this->option('report') === null) {
            return ReportSection::defaults();
        }

        return ReportSection::fromList($this->optionToList('report'));
    }

    private function write(string $rendered): void
    {
        $file = $this->option('output');

        if ($file === null) {
            $this->output->write($rendered, false, OutputInterface::OUTPUT_RAW);

            return;
        }

        file_put_contents($file, $rendered);

        $this->info("Report written to {$file}.");
    }
}
