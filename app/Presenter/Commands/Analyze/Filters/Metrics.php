<?php

namespace App\Presenter\Commands\Analyze\Filters;

class Metrics
{
    private array $metrics = [];

    public function set(array $metrics): void
    {
        $this->metrics = $metrics;
    }

    public function unknown(string $name): bool
    {
        return ! isset($this->metrics[$name]);
    }

    public function get(string $name): array
    {
        return $this->metrics[$name];
    }
}
