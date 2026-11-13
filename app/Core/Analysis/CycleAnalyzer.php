<?php

declare(strict_types=1);

namespace App\Core\Analysis;

use App\Core\Graph\DependencyGraph;

/**
 * Detects dependency cycles between classes and between components.
 *
 * Self-dependencies are removed by GraphBuilder, so a cycle always has more than one member.
 */
final readonly class CycleAnalyzer
{
    public function __construct(
        private ComponentAnalyzer $componentAnalyzer,
    ) {}

    /**
     * @return list<Cycle> Class cycles first, then component cycles, each sorted by size descending then by first member
     */
    public function analyze(DependencyGraph $graph): array
    {
        return [
            ...self::cyclesOf(self::classAdjacency($graph), CycleLevel::Class_),
            ...self::cyclesOf($this->componentAnalyzer->dependencies($graph), CycleLevel::Component),
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private static function classAdjacency(DependencyGraph $graph): array
    {
        $fqcns = array_keys($graph->nodes());

        return array_combine($fqcns, array_map($graph->dependenciesOf(...), $fqcns));
    }

    /**
     * @param  array<string, list<string>>  $adjacency
     * @return list<Cycle>
     */
    private static function cyclesOf(array $adjacency, CycleLevel $level): array
    {
        $components = array_filter(StronglyConnectedComponents::find($adjacency), static fn (array $members): bool => count($members) > 1);

        return array_values(array_map(static fn (array $members): Cycle => new Cycle($members, $level), $components));
    }
}
