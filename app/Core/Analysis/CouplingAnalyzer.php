<?php

declare(strict_types=1);

namespace App\Core\Analysis;

use App\Core\Graph\DependencyGraph;

final readonly class CouplingAnalyzer
{
    /**
     * @return array<string, ClassMetrics> Indexed and sorted by FQCN, like the graph nodes
     */
    public function analyze(DependencyGraph $graph): array
    {
        $metrics = [];

        foreach ($graph->nodes() as $fqcn => $node) {
            $afferent = $graph->afferent($fqcn);
            $efferent = $graph->efferent($fqcn);

            $metrics[$fqcn] = new ClassMetrics(
                $fqcn,
                $node->kind,
                $afferent,
                $efferent,
                count($graph->externalDependenciesOf($fqcn)),
                self::instability($afferent, $efferent),
            );
        }

        return $metrics;
    }

    private static function instability(int $afferent, int $efferent): float
    {
        $coupling = $afferent + $efferent;

        return $coupling === 0 ? 0.0 : $efferent / $coupling;
    }
}
