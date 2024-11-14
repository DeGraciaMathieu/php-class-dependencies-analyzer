<?php

namespace App\Presenter\Analyze\Shared\Network;

class NetworkAttribute
{
    public function __construct(
        private readonly string $name,
        private readonly float $instability,
        private readonly array $dependencies,
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
