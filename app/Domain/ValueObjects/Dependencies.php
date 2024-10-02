<?php

namespace App\Domain\ValueObjects;

use App\Domain\ValueObjects\Fqcn;

class Dependencies
{
    public function __construct(
        public readonly array $values,
    ) {}

    public function knows(Fqcn $fqcn): bool
    {
        return in_array($fqcn->getValue(), $this->values);
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
