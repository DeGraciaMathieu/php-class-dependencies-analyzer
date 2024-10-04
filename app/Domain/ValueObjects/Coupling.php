<?php

namespace App\Domain\ValueObjects;

class Coupling
{
    public function __construct(
        public int $value = 0,
    ) {}

    public static function from(int $value): self
    {
        return new self($value);
    }

    public function increment(): void
    {
        $this->value++;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
