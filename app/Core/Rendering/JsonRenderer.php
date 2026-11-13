<?php

declare(strict_types=1);

namespace App\Core\Rendering;

use App\Core\Analysis\ClassMetrics;
use App\Core\Analysis\ComponentMetrics;
use App\Core\Analysis\Cycle;
use App\Core\Analysis\SdpViolation;
use App\Core\Parser\ClassKind;
use App\Core\Parser\ParseError;
use App\Core\Report\Report;
use App\Core\Rules\BaselineResult;
use App\Core\Rules\Violation;

/**
 * Machine-readable output, schema version 1. Keys are camelCase, floats rounded to 4 decimals,
 * a non-measurable metric is null. "schema", "summary", "violations" and "errors" are always present
 * ("violations" is empty when no rule is configured, holds only the new ones when a baseline is used),
 * "baseline" only when a baseline is used, the other keys only when their section is requested:
 *
 * {
 *   "schema": 1,
 *   "summary": { "files": int, "classes": int, "errors": int },
 *   "classes": [ { "fqcn": string, "kind": "class"|"abstract-class"|"interface"|"trait"|"enum",
 *                  "afferent": int, "efferent": int, "external": int, "instability": float } ],
 *   "components": [ { "name": string, "classes": list<string>, "classCount": int, "abstractCount": int,
 *                     "abstractness": ?float, "afferent": int, "efferent": int,
 *                     "instability": ?float, "distance": ?float } ],
 *   "cycles": [ { "level": "class"|"component", "size": int, "members": list<string> } ],
 *   "sdp": [ { "from": string, "fromInstability": float, "to": string, "toInstability": float, "delta": float } ],
 *   "violations": [ { "rule": string, "fingerprint": string, "message": string, "file": ?string } ],
 *   "baseline": { "baselined": int, "stale": list<string> },
 *   "errors": [ { "file": string, "message": string } ]
 * }
 */
final readonly class JsonRenderer implements Renderer
{
    public const SCHEMA = 1;

    private const PRECISION = 4;

    public function render(Report $report, array $sections, ?array $violations = null, ?BaselineResult $baseline = null): string
    {
        $data = [
            'schema' => self::SCHEMA,
            'summary' => [
                'files' => $report->fileCount,
                'classes' => count($report->classes),
                'errors' => count($report->errors),
            ],
        ];

        foreach (ReportSection::ordered($sections) as $section) {
            $data[$section->value] = match ($section) {
                ReportSection::Classes => $this->classes($report),
                ReportSection::Components => $this->components($report),
                ReportSection::Cycles => $this->cycles($report),
                ReportSection::Sdp => $this->sdp($report),
            };
        }

        $data['violations'] = array_map(static fn (Violation $violation): array => [
            'rule' => $violation->rule,
            'fingerprint' => $violation->fingerprint,
            'message' => $violation->message,
            'file' => $violation->file,
        ], $violations ?? []);

        if ($baseline !== null) {
            $data['baseline'] = [
                'baselined' => $baseline->baselined,
                'stale' => $baseline->stale,
            ];
        }

        $data['errors'] = array_map(static fn (ParseError $error): array => [
            'file' => $error->file,
            'message' => $error->message,
        ], $report->errors);

        return json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR,
        ).PHP_EOL;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function classes(Report $report): array
    {
        return array_map(static fn (ClassMetrics $class): array => [
            'fqcn' => $class->fqcn,
            'kind' => self::kind($class->kind),
            'afferent' => $class->afferent,
            'efferent' => $class->efferent,
            'external' => $class->externalCount,
            'instability' => self::metric($class->instability),
        ], array_values($report->classes));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function components(Report $report): array
    {
        return array_map(static fn (ComponentMetrics $component): array => [
            'name' => $component->name,
            'classes' => $component->classes,
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
    private function cycles(Report $report): array
    {
        return array_map(static fn (Cycle $cycle): array => [
            'level' => $cycle->level->value,
            'size' => $cycle->size,
            'members' => $cycle->members,
        ], $report->cycles);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sdp(Report $report): array
    {
        return array_map(static fn (SdpViolation $violation): array => [
            'from' => $violation->from,
            'fromInstability' => self::metric($violation->fromInstability),
            'to' => $violation->to,
            'toInstability' => self::metric($violation->toInstability),
            'delta' => self::metric($violation->delta),
        ], $report->sdpViolations);
    }

    /**
     * Stable kebab-case form, part of the schema: never derived from the enum case names.
     */
    private static function kind(ClassKind $kind): string
    {
        return match ($kind) {
            ClassKind::Class_ => 'class',
            ClassKind::AbstractClass => 'abstract-class',
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
