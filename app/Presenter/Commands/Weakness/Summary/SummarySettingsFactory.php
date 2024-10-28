<?php

namespace App\Presenter\Commands\Weakness\Summary;

use App\Presenter\Commands\Weakness\WeaknessCommand;
use App\Presenter\Commands\Weakness\Summary\SummarySettings;

class SummarySettingsFactory
{
    public static function make(WeaknessCommand $command): SummarySettings
    {
        return new SummarySettings(
            limit: $command->option('limit') ?? null,
            minDelta: $command->option('min-delta') ?? null,
            debug: $command->option('debug') ?? false,
        );
    }
}
