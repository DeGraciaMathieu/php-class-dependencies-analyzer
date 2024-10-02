<?php

namespace App\Domain\ValueObjects;

class Fqcn
{
    public function __construct(
        private readonly string $value,
    ) {}

    public function looksLike(array $filters): bool
    {
        $pattern = '#' . implode('|', array_map('preg_quote', $filters)) . '#';

        return (bool) preg_match($pattern, $this->value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function is(Fqcn $otherFqcn): bool
    {
        return $this->getValue() === $otherFqcn->getValue();
    }
}
