<?php

namespace App\Presenter\Analyze\Graph\Adapters\Cytoscape;

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
