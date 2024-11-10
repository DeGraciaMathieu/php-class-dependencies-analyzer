<?php

namespace App\Commands\Analyze\Graph;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Graph\GraphSettings;

class GraphSettingsFactory
{
    public function make(Command $command): GraphSettings
    {
        return new GraphSettings(
            debug: $command->option('debug'),
        );
    }
}
