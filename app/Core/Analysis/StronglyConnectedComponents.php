<?php

declare(strict_types=1);

namespace App\Core\Analysis;

/**
 * Strongly connected components of a directed graph given as a raw adjacency list.
 *
 * Iterative Tarjan algorithm: an explicit call stack replaces recursion so that
 * long dependency chains cannot overflow the PHP stack.
 */
final readonly class StronglyConnectedComponents
{
    /**
     * Targets missing from the adjacency keys are ignored.
     *
     * @param  array<string, list<string>>  $adjacency
     * @return list<list<string>> Every component sorted, components sorted by size descending then by first member
     */
    public static function find(array $adjacency): array
    {
        $index = [];
        $lowLink = [];
        $onStack = [];
        $stack = [];
        $components = [];
        $nextIndex = 0;

        foreach (array_keys($adjacency) as $root) {
            if (isset($index[$root])) {
                continue;
            }

            $index[$root] = $lowLink[$root] = $nextIndex++;
            $stack[] = $root;
            $onStack[$root] = true;
            $callStack = [[$root, 0]];

            while ($callStack !== []) {
                $top = count($callStack) - 1;
                [$vertex, $position] = $callStack[$top];

                if ($position < count($adjacency[$vertex])) {
                    $callStack[$top][1]++;
                    $successor = $adjacency[$vertex][$position];

                    if (! isset($adjacency[$successor])) {
                        continue;
                    }

                    if (! isset($index[$successor])) {
                        $index[$successor] = $lowLink[$successor] = $nextIndex++;
                        $stack[] = $successor;
                        $onStack[$successor] = true;
                        $callStack[] = [$successor, 0];
                    } elseif (isset($onStack[$successor])) {
                        $lowLink[$vertex] = min($lowLink[$vertex], $index[$successor]);
                    }

                    continue;
                }

                array_pop($callStack);

                if ($callStack !== []) {
                    $parent = $callStack[count($callStack) - 1][0];
                    $lowLink[$parent] = min($lowLink[$parent], $lowLink[$vertex]);
                }

                if ($lowLink[$vertex] !== $index[$vertex]) {
                    continue;
                }

                $component = [];

                do {
                    $member = array_pop($stack);
                    unset($onStack[$member]);
                    $component[] = $member;
                } while ($member !== $vertex);

                $components[] = $component;
            }
        }

        return self::sorted($components);
    }

    /**
     * @param  list<list<string>>  $components
     * @return list<list<string>>
     */
    private static function sorted(array $components): array
    {
        foreach ($components as &$component) {
            sort($component, SORT_STRING);
        }

        unset($component);

        usort($components, static fn (array $a, array $b): int => count($b) <=> count($a) ?: strcmp($a[0], $b[0]));

        return $components;
    }
}
