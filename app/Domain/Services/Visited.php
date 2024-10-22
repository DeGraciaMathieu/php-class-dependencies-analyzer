<?php

namespace App\Domain\Services;

class Visited
{
    private array $visited = [];

    public function unknown(string $key): bool
    {
        return ! isset($this->visited[$key]);
    }

    public function mark(string $key, bool $value): void
    {
        $this->visited[$key] = $value;
    }

    public function isMarked(string $key): bool
    {
        return $this->visited[$key] ?? false;
    }
}
