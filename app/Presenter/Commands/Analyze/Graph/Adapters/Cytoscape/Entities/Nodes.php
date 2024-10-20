<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Entities;

use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\NameHelper;

class Nodes
{
    public function __construct(
        private array $nodes = [],
        private array $nodeNames = [],
    ) {}

    public function add(string $name, float $instability): void
    {
        $this->nodeNames[] = $name;

        $this->nodes[] = [
            'data' => [
                'id' => $name,
                'instability' => $instability,
            ],
        ];
    }

    public function miss(string $name): bool
    {
        return ! in_array($name, $this->nodeNames);
    }

    public function toArray(): array
    {
        return $this->nodes;
    }
}
