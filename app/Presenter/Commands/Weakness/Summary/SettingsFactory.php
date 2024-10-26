<?php

namespace App\Presenter\Commands\Weakness\Summary;

use App\Presenter\Commands\Weakness\WeaknessCommand;

class SettingsFactory
{
    public static function make(WeaknessCommand $command): Settings
    {
        return new Settings(
            limit: $command->option('limit') ?? null,
            minDelta: $command->option('min-delta') ?? null,
        );
    }
}
