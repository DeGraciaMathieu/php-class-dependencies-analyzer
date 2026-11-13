<?php

declare(strict_types=1);

namespace App\Core\Rules;

/**
 * Violations compared to a baseline.
 *
 * - new: violations absent from the baseline, the only ones that fail the analysis
 * - baselined: number of violations already known by the baseline
 * - stale: fingerprints of the baseline matching no violation anymore, sorted, never blocking
 */
final readonly class BaselineResult
{
    /**
     * @param  list<Violation>  $new
     * @param  list<string>  $stale
     */
    public function __construct(
        public array $new,
        public int $baselined,
        public array $stale,
    ) {}
}
