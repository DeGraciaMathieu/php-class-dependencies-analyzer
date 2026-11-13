<?php

declare(strict_types=1);

namespace App\Core\Graph;

use InvalidArgumentException;

/**
 * Immutable dependency graph, built by GraphBuilder.
 *
 * Every node has an entry (possibly empty) in each adjacency list; nodes are
 * sorted by FQCN and every list is sorted.
 *
 * - out: dependencies towards nodes of this graph
 * - in: reverse index of out
 * - external: dependencies towards classes never analysed (vendor, native PHP...)
 * - filteredOut: dependencies towards analysed classes removed by subgraph()
 */
final readonly class DependencyGraph
{
    /**
     * @param  array<string, Node>  $nodes
     * @param  array<string, list<string>>  $out
     * @param  array<string, list<string>>  $in
     * @param  array<string, list<string>>  $external
     * @param  array<string, list<string>>  $filteredOut
     * @param  list<string>  $duplicates
     */
    public function __construct(
        private array $nodes,
        private array $out,
        private array $in,
        private array $external,
        private array $filteredOut,
        private array $duplicates,
    ) {}

    /**
     * @return array<string, Node>
     */
    public function nodes(): array
    {
        return $this->nodes;
    }

    public function has(string $fqcn): bool
    {
        return isset($this->nodes[$fqcn]);
    }

    public function node(string $fqcn): Node
    {
        return $this->nodes[$fqcn] ?? throw new InvalidArgumentException("Unknown class [{$fqcn}].");
    }

    /**
     * @return list<string>
     */
    public function dependenciesOf(string $fqcn): array
    {
        return $this->out[$fqcn] ?? [];
    }

    /**
     * @return list<string>
     */
    public function dependentsOf(string $fqcn): array
    {
        return $this->in[$fqcn] ?? [];
    }

    /**
     * @return list<string>
     */
    public function externalDependenciesOf(string $fqcn): array
    {
        return $this->external[$fqcn] ?? [];
    }

    /**
     * @return list<string>
     */
    public function filteredOutDependenciesOf(string $fqcn): array
    {
        return $this->filteredOut[$fqcn] ?? [];
    }

    public function efferent(string $fqcn): int
    {
        return count($this->dependenciesOf($fqcn));
    }

    public function afferent(string $fqcn): int
    {
        return count($this->dependentsOf($fqcn));
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * FQCNs declared more than once in the analysed sources (only the first declaration is kept).
     *
     * @return list<string>
     */
    public function duplicates(): array
    {
        return $this->duplicates;
    }

    /**
     * Returns a new graph restricted to the nodes matched by the filter.
     *
     * Dependencies towards removed nodes are moved to filteredOut (never to external),
     * and edges coming from removed nodes are dropped from in. Metrics that need the
     * full graph (e.g. afferent coupling) must therefore be computed before subgraphing.
     */
    public function subgraph(Filter $filter): self
    {
        $nodes = array_filter($this->nodes, static fn (Node $node): bool => $filter->matches($node->fqcn));

        $out = [];
        $in = [];
        $external = [];
        $filteredOut = [];

        foreach (array_keys($nodes) as $fqcn) {
            $removed = array_values(array_filter($this->out[$fqcn], static fn (string $dependency): bool => ! isset($nodes[$dependency])));
            $mergedFilteredOut = [...$this->filteredOut[$fqcn], ...$removed];

            sort($mergedFilteredOut, SORT_STRING);

            $out[$fqcn] = self::keepOnly($this->out[$fqcn], $nodes);
            $in[$fqcn] = self::keepOnly($this->in[$fqcn], $nodes);
            $external[$fqcn] = $this->external[$fqcn];
            $filteredOut[$fqcn] = $mergedFilteredOut;
        }

        $duplicates = array_values(array_filter($this->duplicates, $filter->matches(...)));

        return new self($nodes, $out, $in, $external, $filteredOut, $duplicates);
    }

    /**
     * @param  list<string>  $fqcns
     * @param  array<string, Node>  $nodes
     * @return list<string>
     */
    private static function keepOnly(array $fqcns, array $nodes): array
    {
        return array_values(array_filter($fqcns, static fn (string $fqcn): bool => isset($nodes[$fqcn])));
    }
}
