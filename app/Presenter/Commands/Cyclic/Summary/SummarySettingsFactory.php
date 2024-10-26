<?php

namespace App\Presenter\Commands\Cyclic\Summary;

use App\Presenter\Commands\Cyclic\CyclicCommand;
use App\Presenter\Commands\Cyclic\Summary\SummarySettings;

class SummarySettingsFactory
{
    public static function make(CyclicCommand $command): SummarySettings
    {
        return new SummarySettings(
            debug: $command->option('debug'),
        );
    }
}
