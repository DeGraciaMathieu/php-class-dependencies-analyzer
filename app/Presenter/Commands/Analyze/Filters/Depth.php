<?php

namespace App\Presenter\Commands\Analyze\Filters;

class Depth
{
    private array $depth = [];

    public function add(array $class): void
    {
        $this->depth[$class['name']] = $class;
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
