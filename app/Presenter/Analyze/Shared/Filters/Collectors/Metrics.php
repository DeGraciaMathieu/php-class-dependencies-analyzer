<?php

namespace App\Presenter\Analyze\Shared\Filters\Collectors;

use App\Application\Analyze\AnalyzeMetric;

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

    public function get(string $name): AnalyzeMetric
    {
        return $this->metrics[$name];
    }
}
