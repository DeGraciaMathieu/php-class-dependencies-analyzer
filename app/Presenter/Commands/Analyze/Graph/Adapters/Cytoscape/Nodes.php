<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape;

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
        return ! in_array($name, $this->nodeNames, true);
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    public function toArray(): array
    {
        return $this->nodes;
    }
}
