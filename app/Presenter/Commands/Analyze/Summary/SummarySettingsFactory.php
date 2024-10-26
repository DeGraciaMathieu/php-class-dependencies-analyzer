<?php

namespace App\Presenter\Commands\Analyze\Summary;

use App\Presenter\Commands\Analyze\AnalyzeCommand;
use App\Presenter\Commands\Analyze\Summary\SummarySettings;

class SummarySettingsFactory
{
    public static function make(AnalyzeCommand $command): SummarySettings
    {
        return new SummarySettings(
            debug: $command->option('debug'),
        );
    }
}
