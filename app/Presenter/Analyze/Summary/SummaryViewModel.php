<?php

namespace App\Presenter\Analyze\Summary;

use App\Presenter\Analyze\Summary\SummarySettings;

class SummaryViewModel
{
    public function __construct(
        private readonly array $metrics,
        private readonly int $count,
        private readonly SummarySettings $settings,
    ) {}

    public function headers(): array
    {
        $metrics = array_values($this->metrics);

        return array_keys($metrics[0]);
    }

    public function isHumanReadable(): bool
    {
        return $this->settings->humanReadable;
    }

    public function needInfo(): bool
    {
        return $this->settings->info;
    }

    public function metrics(): array
    {
        return $this->metrics;
    }

    public function count(): int
    {
        return $this->count;
    }
}
