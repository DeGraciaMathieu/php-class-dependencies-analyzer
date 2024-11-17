<?php

namespace App\Presenter\Analyze\Component\Summary;

class SummaryViewModel
{
    public function __construct(
        public readonly array $components,
    ) {}

    public function components(): array
    {
        return $this->components;
    }

    public function headers(): array
    {
        return array_keys(array_values($this->components)[0]);
    }

    public function count(): int
    {
        return count($this->components);
    }

    public function hasComponents(): bool
    {
        return $this->count() > 0;
    }

    public function needInfo(): bool
    {
        return false;
    }

    public function isHumanReadable(): bool
    {
        return false;
    }
}
