<?php

namespace App\Commands\Analyze;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Summary\SummarySettings;

class SummarySettingsFactory
{
    public function make(Command $command): SummarySettings
    {
        return new SummarySettings(
            debug: $command->option('debug'),
            info: $command->option('info'),
            humanReadable: $command->option('h'),
        );
    }
}
