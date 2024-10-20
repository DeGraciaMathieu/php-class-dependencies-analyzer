<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Entities;

use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\NameHelper;

class Edges
{
    public function __construct(
        private array $edges = [],
    ) {}

    public function add(string $source, string $target): void
    {
        $this->edges[] = [
            'data' => [
                'source' => $source,
                'target' => $target,
            ],
        ];
    }

    public function toArray(): array
    {
        return $this->edges;
    }
}
