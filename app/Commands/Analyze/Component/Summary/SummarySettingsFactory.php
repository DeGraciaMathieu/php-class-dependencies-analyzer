<?php

namespace App\Commands\Analyze\Component\Summary;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Component\Summary\SummarySettings;

class SummarySettingsFactory
{
    public function make(Command $command): SummarySettings
    {
        return new SummarySettings(
            components: $command->argumentToList('components'),
            info: $command->option('info'),
            debug: $command->option('debug'),
        );
    }
}
