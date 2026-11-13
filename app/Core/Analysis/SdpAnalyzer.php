<?php

declare(strict_types=1);

namespace App\Core\Analysis;

use App\Core\Graph\DependencyGraph;

/**
 * Detects Stable Dependencies Principle violations between components.
 *
 * Every dependency towards a strictly less stable component is reported, without tolerance.
 * Components whose instability is not measurable are skipped.
 */
final readonly class SdpAnalyzer
{
    public function __construct(
        private ComponentAnalyzer $componentAnalyzer,
    ) {}

    /**
     * @return list<SdpViolation> Sorted by delta descending, then by from, then by to
     */
    public function analyze(DependencyGraph $graph): array
    {
        $metrics = $this->componentAnalyzer->analyze($graph);

        $violations = [];

        foreach ($this->componentAnalyzer->dependencies($graph) as $from => $targets) {
            foreach ($targets as $to) {
                $fromInstability = $metrics[$from]->instability;
                $toInstability = $metrics[$to]->instability;

                if ($fromInstability === null || $toInstability === null) {
                    continue;
                }

                if ($toInstability > $fromInstability) {
                    $violations[] = new SdpViolation($from, $fromInstability, $to, $toInstability, $toInstability - $fromInstability);
                }
            }
        }

        usort($violations, static fn (SdpViolation $a, SdpViolation $b): int => $b->delta <=> $a->delta ?: strcmp($a->from, $b->from) ?: strcmp($a->to, $b->to));

        return $violations;
    }
}
