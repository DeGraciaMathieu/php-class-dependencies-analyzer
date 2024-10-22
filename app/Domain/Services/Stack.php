<?php

namespace App\Domain\Services;

class Stack
{
    public array $stack = [];

    public function contains(string $class): bool
    {
        return in_array($class, $this->stack, true);
    }

    public function push(string $class): void
    {
        $this->stack[] = $class;
    }

    public function pop(): void
    {
        array_pop($this->stack);
    }
}
