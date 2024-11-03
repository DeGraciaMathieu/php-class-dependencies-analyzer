<?php

namespace App\Presenter\Commands\Analyze\Summary;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Commands\Analyze\Summary\SummarySettings;

class SummarySettingsFactory
{
    public static function make(Command $command): SummarySettings
    {
        return new SummarySettings(
            debug: $command->option('debug'),
        );
    }
}
