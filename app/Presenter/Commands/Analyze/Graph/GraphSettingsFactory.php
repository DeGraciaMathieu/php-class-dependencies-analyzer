<?php

namespace App\Presenter\Commands\Analyze\Graph;

use App\Presenter\Commands\Analyze\AnalyzeCommand;
use App\Presenter\Commands\Analyze\Graph\GraphSettings;

class GraphSettingsFactory
{
    public static function make(AnalyzeCommand $command): GraphSettings
    {
        return new GraphSettings(
            debug: $command->option('debug'),
        );
    }
}
