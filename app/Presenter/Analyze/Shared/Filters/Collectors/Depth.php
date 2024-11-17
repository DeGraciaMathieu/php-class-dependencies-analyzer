<?php

namespace App\Presenter\Analyze\Shared\Filters\Collectors;

use App\Application\Analyze\AnalyzeMetric;

class Depth
{
    private array $depth = [];

    public function add(AnalyzeMetric $class): void
    {
        $this->depth[$class->name()] = $class;
    }

    public function has(string $name): bool
    {
        return isset($this->depth[$name]);
    }

    public function toArray(): array
    {
        return $this->depth;
    }
}
