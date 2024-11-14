<?php

namespace App\Presenter\Analyze\Component\Shared;

use App\Presenter\Analyze\Shared\Network\Networkable;

class Component implements Networkable
{
    public function __construct(
        public readonly string $name,   
        public readonly int $countClasses,  
        public readonly float $countAbstractions,   
        public readonly float $averageAbstractness, 
        public readonly float $averageInstability,  
        public readonly array $innerDependencies,   
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function countClasses(): int
    {
        return $this->countClasses;
    }

    public function countAbstractions(): int
    {
        return $this->countAbstractions;
    }

    public function abstractness(): float
    {
        return number_format($this->averageAbstractness, 2);
    }

    public function instability(): float
    {
        return number_format($this->averageInstability, 2);
    }

    public function dependencies(): array
    {
        return $this->innerDependencies;
    }
}
