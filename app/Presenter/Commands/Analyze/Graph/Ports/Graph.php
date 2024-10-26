<?php

namespace App\Presenter\Commands\Analyze\Graph\Ports;

interface Graph
{
    public function addNode(string $name, float $instability = 0): void;
    public function missingNode(string $name): bool;
    public function addEdge(string $source, string $target): void;
    public function toArray(): array;
}
