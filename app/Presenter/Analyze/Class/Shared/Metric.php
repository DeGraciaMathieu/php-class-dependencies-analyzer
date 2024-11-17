<?php

namespace App\Presenter\Analyze\Class\Shared;

use App\Presenter\Analyze\Shared\Network\Networkable;

class Metric implements Networkable
{
    public function __construct(
        private string $name,
        private float $instability,
        private array $dependencies,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function instability(): float
    {
        return $this->instability;
    }

    public function dependencies(): array
    {
        return $this->dependencies;
    }
}