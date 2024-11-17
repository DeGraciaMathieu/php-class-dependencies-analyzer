<?php

namespace App\Presenter\Analyze\Shared\Filters\Collectors;

class Components 
{
    public function __construct(
        private array $components,
    ) {}

    public function set(array $components): void
    {
        $this->components = $components;
    }

    public function get(): array
    {
        return $this->components;
    }

    public function add(string $component, array $metric): void
    {
        $this->components[$component][] = $metric;
    }
}
