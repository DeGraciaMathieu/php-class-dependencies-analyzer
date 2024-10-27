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

    public function extractCycle(string $class): array
    {
        /**
         * Get the index of the class in the stack.
         */
        $index = array_search($class, $this->stack);

        /**
         * Get the cycle by slicing the stack from the index of the class to the end.
         */
        return array_slice($this->stack, $index);
    }

    public function pop(): void
    {
        array_pop($this->stack);
    }
}
