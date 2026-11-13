<?php

declare(strict_types=1);

namespace App\Core\Report;

use App\Core\Analysis\ClassMetrics;
use App\Core\Analysis\ComponentMetrics;
use App\Core\Analysis\Cycle;
use App\Core\Analysis\SdpViolation;
use App\Core\Parser\ParseError;

/**
 * Result of an analysis: metrics computed on the full graph, then restricted to the filtered entries.
 */
final readonly class Report
{
    /**
     * @param  array<string, ClassMetrics>  $classes  Indexed and sorted by FQCN
     * @param  array<string, ComponentMetrics>  $components  Indexed and sorted by component name
     * @param  list<Cycle>  $cycles
     * @param  list<SdpViolation>  $sdpViolations
     * @param  list<ParseError>  $errors
     * @param  list<string>  $duplicates
     * @param  array<string, list<string>>  $componentDependencies  Retained component => retained components it depends on
     * @param  array<string, list<string>>  $classDependencies  Retained FQCN => retained FQCNs it depends on
     */
    public function __construct(
        public array $classes,
        public array $components,
        public array $cycles,
        public array $sdpViolations,
        public array $errors,
        public array $duplicates,
        public int $fileCount,
        public array $componentDependencies = [],
        public array $classDependencies = [],
    ) {}
}
