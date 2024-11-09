<?php

namespace App\Domain\ValueObjects;

abstract class SimpleBoolean
{
    public function __construct(
        public bool $value = false,
    ) {}

    public function isTrue(): bool
    {
        return (bool) $this->value;
    }

    public static function fromBool(bool $value): self
    {
        return new static($value);
    }
}
