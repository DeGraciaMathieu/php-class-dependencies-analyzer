<?php

declare(strict_types=1);

namespace App\Core\Rendering;

use App\Core\Analysis\ClassMetrics;
use App\Core\Analysis\ComponentMetrics;
use App\Core\Analysis\Cycle;
use App\Core\Analysis\SdpViolation;
use App\Core\Parser\ClassKind;
use App\Core\Report\BaselineResult;
use App\Core\Report\Report;

/**
 * Standalone HTML page exploring the graph with Cytoscape: a component view and a class view (grouped by component),
 * cycles and SDP violations in red, a side panel with the metrics and dependencies of the clicked node.
 * Every section is always rendered, the requested sections are ignored.
 *
 * The report data is injected as JSON in a non-executable script element: every character able to close the element
 * or break out of it (<, >, &, ', ") is escaped as \uXXXX.
 */
final readonly class HtmlRenderer implements Renderer
{
    public const CYTOSCAPE_VERSION = '3.21.1';

    private const TEMPLATE = __DIR__.'/html-report.html';

    private const PRECISION = 4;

    public function render(Report $report, array $sections, ?array $violations = null, ?BaselineResult $baseline = null): string
    {
        $data = [
            'summary' => [
                'files' => $report->fileCount,
                'classes' => count($report->classes),
                'components' => count($report->components),
                'cycles' => count($report->cycles),
                'sdpViolations' => count($report->sdpViolations),
                'ruleViolations' => $violations === null ? null : count($violations),
                'errors' => count($report->errors),
            ],
            'components' => $this->components($report),
            'classes' => $this->classes($report),
            'componentDependencies' => $report->componentDependencies,
            'classDependencies' => $report->classDependencies,
            'cycles' => array_map(static fn (Cycle $cycle): array => [
                'level' => $cycle->level->value,
                'members' => $cycle->members,
            ], $report->cycles),
            'sdp' => array_map(static fn (SdpViolation $violation): array => [
                'from' => $violation->from,
                'to' => $violation->to,
            ], $report->sdpViolations),
        ];

        $json = json_encode(
            $data,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR,
        );

        return strtr((string) file_get_contents(self::TEMPLATE), [
            '{{CYTOSCAPE_VERSION}}' => self::CYTOSCAPE_VERSION,
            '{{REPORT_DATA}}' => $json,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function components(Report $report): array
    {
        return array_map(static fn (ComponentMetrics $component): array => [
            'name' => $component->name,
            'classCount' => $component->classCount,
            'abstractCount' => $component->abstractCount,
            'abstractness' => self::metric($component->abstractness),
            'afferent' => $component->afferent,
            'efferent' => $component->efferent,
            'instability' => self::metric($component->instability),
            'distance' => self::metric($component->distance),
        ], array_values($report->components));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function classes(Report $report): array
    {
        $componentOf = [];

        foreach ($report->components as $component) {
            foreach ($component->classes as $fqcn) {
                $componentOf[$fqcn] = $component->name;
            }
        }

        return array_map(static fn (ClassMetrics $class): array => [
            'fqcn' => $class->fqcn,
            'kind' => self::kind($class->kind),
            'component' => $componentOf[$class->fqcn],
            'afferent' => $class->afferent,
            'efferent' => $class->efferent,
            'external' => $class->externalCount,
            'instability' => self::metric($class->instability),
        ], array_values($report->classes));
    }

    private static function kind(ClassKind $kind): string
    {
        return match ($kind) {
            ClassKind::Class_ => 'class',
            ClassKind::AbstractClass => 'abstract class',
            ClassKind::Interface_ => 'interface',
            ClassKind::Trait_ => 'trait',
            ClassKind::Enum_ => 'enum',
        };
    }

    private static function metric(?float $value): ?float
    {
        return $value === null ? null : round($value, self::PRECISION);
    }
}
