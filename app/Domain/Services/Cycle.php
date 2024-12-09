<?php

namespace App\Domain\Services;

class Cycle
{
    public array $cycles = [];

    public function add(array $cycle): void
    {
        $this->cycles[] = $cycle;
    }

    public function isEmpty(): bool
    {
        return empty($this->cycles);
    }

    public function count(): int
    {
        return count($this->cycles);
    }

    public function all(): array
    {
        return $this->cycles;
    }
}
