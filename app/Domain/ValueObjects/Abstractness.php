<?php

namespace App\Domain\ValueObjects;

class Abstractness
{
    public function __construct(
        private float $ratio = 0,
        private int $numberOfAbstractDependencies = 0
    ) {}

    public function getNumberOfAbstractDependencies(): int
    {
        return $this->numberOfAbstractDependencies;
    }

    public function increment(): void
    {
        $this->numberOfAbstractDependencies++;
    }

    public function calculate(int $totalDependencies): void
    {
        $this->ratio = $totalDependencies === 0
            ? 0
            : number_format($this->numberOfAbstractDependencies / $totalDependencies, 2);
    }

    public function toArray(): array
    {
        return [
            'ratio' => $this->ratio,
            'numberOfAbstractDependencies' => $this->numberOfAbstractDependencies,
        ];
    }
}
