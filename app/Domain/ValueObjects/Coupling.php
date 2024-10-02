<?php

namespace App\Domain\ValueObjects;

class Coupling
{
    public function __construct(
        public int $value = 0,
    ) {}

    public function increment(): void
    {
        $this->value++;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
