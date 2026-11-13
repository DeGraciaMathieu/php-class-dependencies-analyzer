<?php

declare(strict_types=1);

namespace App\Core\Graph;

use App\Core\Parser\ParsedClass;

final class GraphBuilder
{
    /**
     * Builds the graph in O(n·d). On duplicate FQCNs the first class met is kept
     * and the FQCN is reported through DependencyGraph::duplicates().
     *
     * @param  iterable<ParsedClass>  $classes
     */
    public function build(iterable $classes): DependencyGraph
    {
        $nodes = [];
        $dependencies = [];
        $duplicates = [];

        foreach ($classes as $class) {
            if (isset($nodes[$class->fqcn])) {
                $duplicates[$class->fqcn] = true;

                continue;
            }

            $nodes[$class->fqcn] = new Node($class->fqcn, $class->kind, $class->file);
            $dependencies[$class->fqcn] = $class->dependencies;
        }

        ksort($nodes, SORT_STRING);

        $out = array_fill_keys(array_keys($nodes), []);
        $in = $out;
        $external = $out;

        foreach ($dependencies as $fqcn => $classDependencies) {
            foreach ($classDependencies as $dependency) {
                if ($dependency === $fqcn) {
                    continue;
                }

                if (isset($nodes[$dependency])) {
                    $out[$fqcn][$dependency] = true;
                    $in[$dependency][$fqcn] = true;
                } else {
                    $external[$fqcn][$dependency] = true;
                }
            }
        }

        return new DependencyGraph(
            nodes: $nodes,
            out: array_map(self::sortedKeys(...), $out),
            in: array_map(self::sortedKeys(...), $in),
            external: array_map(self::sortedKeys(...), $external),
            filteredOut: array_fill_keys(array_keys($nodes), []),
            duplicates: self::sortedKeys($duplicates),
        );
    }

    /**
     * @param  array<string, true>  $set
     * @return list<string>
     */
    private static function sortedKeys(array $set): array
    {
        $keys = array_map('strval', array_keys($set));

        sort($keys, SORT_STRING);

        return $keys;
    }
}
