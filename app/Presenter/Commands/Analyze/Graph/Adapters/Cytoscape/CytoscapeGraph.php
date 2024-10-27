<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape;

use App\Presenter\Commands\Analyze\Graph\Ports\Graph;
use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Edges;
use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Nodes;

class CytoscapeGraph implements Graph
{
    public function __construct(
        private Nodes $nodes,
        private Edges $edges,
    ) {}

    public function addNode(string $name, float $instability = 0): void
    {
        $this->nodes->add($name, $instability);
    }

    public function countNodes(): int
    {
        return $this->nodes->count();
    }

    public function missingNode(string $name): bool
    {
        return $this->nodes->miss($name);
    }

    public function addEdge(string $source, string $target): void
    {
        $this->edges->add($source, $target);
    }

    public function toArray(): array
    {
        return [
            'nodes' => $this->nodes->toArray(),
            'edges' => $this->edges->toArray(),
        ];
    }
}
