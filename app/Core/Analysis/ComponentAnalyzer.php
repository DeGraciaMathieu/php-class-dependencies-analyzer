<?php

declare(strict_types=1);

namespace App\Core\Analysis;

use App\Core\Graph\DependencyGraph;
use App\Core\Graph\Node;
use App\Core\Parser\ClassKind;

/**
 * Groups the graph classes into components and measures them.
 *
 * Dependencies between classes of the same component are ignored;
 * traits are excluded from Nc and Na but their edges count for coupling.
 */
final readonly class ComponentAnalyzer
{
    public function __construct(
        private ComponentResolver $resolver,
    ) {}

    /**
     * @return array<string, ComponentMetrics> Indexed and sorted by component name
     */
    public function analyze(DependencyGraph $graph): array
    {
        $componentOf = $this->resolveComponents($graph);

        $metrics = [];

        foreach (self::groupByComponent($componentOf) as $component => $classes) {
            $metrics[$component] = self::measure($graph, $componentOf, $component, $classes);
        }

        return $metrics;
    }

    /**
     * @return array<string, list<string>> Component => sorted components it depends on, sorted by component name
     */
    public function dependencies(DependencyGraph $graph): array
    {
        $componentOf = $this->resolveComponents($graph);

        $dependencies = [];

        foreach (self::groupByComponent($componentOf) as $component => $classes) {
            $outsideDependencies = self::outsideDependenciesOf($graph, $componentOf, $component, $classes);
            $dependedComponents = array_values(array_unique(array_map(static fn (string $fqcn): string => $componentOf[$fqcn], $outsideDependencies)));

            sort($dependedComponents, SORT_STRING);

            $dependencies[$component] = $dependedComponents;
        }

        return $dependencies;
    }

    /**
     * @return array<string, string> FQCN => component
     */
    private function resolveComponents(DependencyGraph $graph): array
    {
        $componentOf = [];

        foreach (array_keys($graph->nodes()) as $fqcn) {
            $componentOf[$fqcn] = $this->resolver->resolve($fqcn);
        }

        return $componentOf;
    }

    /**
     * @param  array<string, string>  $componentOf
     * @return array<string, list<string>>
     */
    private static function groupByComponent(array $componentOf): array
    {
        $groups = [];

        foreach ($componentOf as $fqcn => $component) {
            $groups[$component][] = $fqcn;
        }

        ksort($groups, SORT_STRING);

        return $groups;
    }

    /**
     * @param  array<string, string>  $componentOf
     * @param  list<string>  $classes
     */
    private static function measure(DependencyGraph $graph, array $componentOf, string $component, array $classes): ComponentMetrics
    {
        $nodes = array_map($graph->node(...), $classes);

        $classCount = count(array_filter($nodes, static fn (Node $node): bool => $node->kind !== ClassKind::Trait_));
        $abstractCount = count(array_filter($nodes, static fn (Node $node): bool => $node->kind->isAbstract()));
        $afferent = count(self::outsideDependentsOf($graph, $componentOf, $component, $classes));
        $efferent = count(self::outsideDependenciesOf($graph, $componentOf, $component, $classes));

        $abstractness = $classCount === 0 ? null : $abstractCount / $classCount;
        $coupling = $afferent + $efferent;
        $instability = $coupling === 0 ? null : $efferent / $coupling;
        $distance = $abstractness === null || $instability === null ? null : abs($abstractness + $instability - 1);

        return new ComponentMetrics(
            $component,
            $classes,
            $classCount,
            $abstractCount,
            $abstractness,
            $afferent,
            $efferent,
            $instability,
            $distance,
        );
    }

    /**
     * @param  array<string, string>  $componentOf
     * @param  list<string>  $classes
     * @return list<string>
     */
    private static function outsideDependenciesOf(DependencyGraph $graph, array $componentOf, string $component, array $classes): array
    {
        $dependencies = array_merge(...array_map($graph->dependenciesOf(...), $classes));

        return self::outside($dependencies, $componentOf, $component);
    }

    /**
     * @param  array<string, string>  $componentOf
     * @param  list<string>  $classes
     * @return list<string>
     */
    private static function outsideDependentsOf(DependencyGraph $graph, array $componentOf, string $component, array $classes): array
    {
        $dependents = array_merge(...array_map($graph->dependentsOf(...), $classes));

        return self::outside($dependents, $componentOf, $component);
    }

    /**
     * @param  list<string>  $fqcns
     * @param  array<string, string>  $componentOf
     * @return list<string> Distinct FQCNs belonging to another component
     */
    private static function outside(array $fqcns, array $componentOf, string $component): array
    {
        $outsideFqcns = array_filter($fqcns, static fn (string $fqcn): bool => $componentOf[$fqcn] !== $component);

        return array_values(array_unique($outsideFqcns));
    }
}
