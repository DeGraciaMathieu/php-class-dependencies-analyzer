<?php

namespace App\Commands\Analyze\Class\Summary;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Class\Summary\SummarySettings;

class SummarySettingsFactory
{
    public function make(Command $command): SummarySettings
    {
        return new SummarySettings(
            debug: $command->option('debug'),
            info: $command->option('info'),
            humanReadable: $command->option('human-readable'),
        );
    }
}
