<?php

namespace App\Commands\Analyze\Component\Graph;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Component\Graph\GraphSettings;

class GraphSettingsFactory
{
    public function make(Command $command): GraphSettings
    {
        return new GraphSettings(
            components: $command->argumentToList('components'),
            info: $command->option('info'),
            debug: $command->option('debug'),
        );
    }
}
