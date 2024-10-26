<?php

namespace App\Presenter\Commands\Weakness\Summary;

class Settings
{
    public function __construct(
        private readonly ?int $limit = null,
        private readonly ?float $minDelta = null,
    ) {}

    public function limit(): ?int
    {
        return $this->limit;
    }

    public function minDelta(): float
    {
        return $this->minDelta ?? 0.0;
    }
}
