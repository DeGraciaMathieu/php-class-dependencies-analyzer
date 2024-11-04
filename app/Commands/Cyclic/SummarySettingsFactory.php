<?php

namespace App\Commands\Cyclic;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Cyclic\Summary\SummarySettings;

class SummarySettingsFactory
{
    public function make(Command $command): SummarySettings
    {
        return new SummarySettings(
            debug: $command->option('debug'),
        );
    }
}
