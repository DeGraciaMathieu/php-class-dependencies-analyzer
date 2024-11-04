<?php

namespace App\Presenter\Weakness\Summary;

class SummarySettings
{
    public function __construct(
        private readonly ?int $limit = null,
        private readonly ?float $minDelta = null,
        private readonly bool $debug = false,
    ) {}

    public function limit(): ?int
    {
        return $this->limit;
    }

    public function minDelta(): float
    {
        return $this->minDelta ?? 0.0;
    }

    public function debug(): bool
    {
        return $this->debug;
    }
}
