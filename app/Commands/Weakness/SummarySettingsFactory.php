<?php

namespace App\Commands\Weakness;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Weakness\Summary\SummarySettings;

class SummarySettingsFactory
{
    public function make(Command $command): SummarySettings
    {
        return new SummarySettings(
            limit: $command->option('limit') ?? null,
            minDelta: $command->option('min-delta') ?? null,
            debug: $command->option('debug') ?? false,
        );
    }
}
