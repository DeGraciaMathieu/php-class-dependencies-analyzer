<?php

namespace App\Presenter\Commands\Analyze\Graph;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Commands\Analyze\Graph\GraphSettings;

class GraphSettingsFactory
{
    public static function make(Command $command): GraphSettings
    {
        return new GraphSettings(
            debug: $command->option('debug'),
        );
    }
}
