<?php

declare(strict_types=1);

namespace App\Core\Rendering;

use App\Core\Analysis\ClassMetrics;
use App\Core\Analysis\ComponentMetrics;
use App\Core\Analysis\Cycle;
use App\Core\Analysis\SdpViolation;
use App\Core\Parser\ParseError;
use App\Core\Report\BaselineResult;
use App\Core\Report\Report;
use App\Core\Report\Violation;

/**
 * Human-readable terminal output: a summary line, then one table per requested section (enum order),
 * then the rule violations when rules are configured (followed by the baseline counts when a baseline is used),
 * then the parsing errors if any.
 * Metrics are rounded to 2 decimals, a null metric is shown as "—".
 */
final readonly class TableRenderer implements Renderer
{
    private const NOT_MEASURABLE = '—';

    public function render(Report $report, array $sections, ?array $violations = null, ?BaselineResult $baseline = null): string
    {
        $blocks = [$this->summary($report)];

        foreach (ReportSection::ordered($sections) as $section) {
            $blocks[] = match ($section) {
                ReportSection::Classes => $this->classes($report),
                ReportSection::Components => $this->components($report),
                ReportSection::Cycles => $this->cycles($report),
                ReportSection::Sdp => $this->sdp($report),
            };
        }

        if ($violations !== null) {
            $blocks[] = $this->violations($violations, $baseline);
        }

        if ($report->errors !== []) {
            $blocks[] = $this->errors($report);
        }

        return implode(PHP_EOL.PHP_EOL, $blocks).PHP_EOL;
    }

    private function summary(Report $report): string
    {
        return sprintf(
            'Files: %d, classes: %d, errors: %d',
            $report->fileCount,
            count($report->classes),
            count($report->errors),
        );
    }

    private function classes(Report $report): string
    {
        $rows = array_map(static fn (ClassMetrics $class): array => [
            $class->fqcn,
            (string) $class->afferent,
            (string) $class->efferent,
            (string) $class->externalCount,
            self::metric($class->instability),
        ], array_values($report->classes));

        return $this->section('Classes', ['Class', 'Ca', 'Ce', 'Ext', 'I'], $rows, 'No class.');
    }

    private function components(Report $report): string
    {
        $rows = array_map(static fn (ComponentMetrics $component): array => [
            $component->name,
            (string) $component->classCount,
            (string) $component->abstractCount,
            self::metric($component->abstractness),
            (string) $component->afferent,
            (string) $component->efferent,
            self::metric($component->instability),
            self::metric($component->distance),
        ], array_values($report->components));

        return $this->section('Components', ['Component', 'Nc', 'Na', 'A', 'Ca', 'Ce', 'I', 'D'], $rows, 'No component.');
    }

    private function cycles(Report $report): string
    {
        $rows = array_map(static fn (Cycle $cycle): array => [
            $cycle->level->value,
            (string) $cycle->size,
            implode(' → ', $cycle->members),
        ], $report->cycles);

        return $this->section('Cycles', ['Level', 'Size', 'Members'], $rows, 'No cycle.');
    }

    private function sdp(Report $report): string
    {
        $rows = array_map(static fn (SdpViolation $violation): array => [
            sprintf(
                '%s (%s) → %s (%s)',
                $violation->from,
                self::metric($violation->fromInstability),
                $violation->to,
                self::metric($violation->toInstability),
            ),
            self::metric($violation->delta),
        ], $report->sdpViolations);

        return $this->section('SDP violations', ['Dependency', 'Δ'], $rows, 'No SDP violation.');
    }

    /**
     * @param  list<Violation>  $violations
     */
    private function violations(array $violations, ?BaselineResult $baseline): string
    {
        $rows = array_map(static fn (Violation $violation): array => [
            $violation->rule,
            $violation->message,
        ], $violations);

        $section = $this->section('Rule violations', ['Rule', 'Message'], $rows, 'No rule violation.');

        if ($baseline === null) {
            return $section;
        }

        return $section.PHP_EOL.sprintf('%d baselined, %d stale', $baseline->baselined, count($baseline->stale));
    }

    private function errors(Report $report): string
    {
        $lines = array_map(
            static fn (ParseError $error): string => sprintf('- %s: %s', $error->file, $error->message),
            $report->errors,
        );

        return implode(PHP_EOL, ['Errors', ...$lines]);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     */
    private function section(string $title, array $headers, array $rows, string $emptyMessage): string
    {
        $body = $rows === [] ? $emptyMessage : (new TextTable($headers, $rows))->render();

        return $title.PHP_EOL.$body;
    }

    private static function metric(?float $value): string
    {
        return $value === null ? self::NOT_MEASURABLE : number_format($value, 2, '.', '');
    }
}
