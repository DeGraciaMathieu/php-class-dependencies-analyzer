<?php

namespace App\Domain\ValueObjects;

class Coupling
{
    public function __construct(
        public int $efferent = 0,
        public int $afferent = 0,
        public float $instability = 0,
    ) {}

    public function incrementAfferent(): void
    {
        $this->afferent++;
    }

    public function nobodyUsesThis(): bool
    {
        return $this->afferent === 0;
    }

    public function calculateInstability(): void
    {
        $instability = $this->efferent / (($this->afferent + $this->efferent) ?: 1);

        $this->instability = number_format($instability, 2);
    }

    public function toArray(): array
    {
        return [
            'afferent' => $this->afferent,
            'efferent' => $this->efferent,
            'instability' => $this->instability,
        ];
    }
}
