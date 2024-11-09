<?php

namespace App\Commands\Analyze;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Component\ComponentSettings;

class ComponentSettingsFactory
{
    public function make(Command $command): ComponentSettings
    {
        return new ComponentSettings(
            components: $command->stringToList('components'),
            debug: $command->option('debug'),
            info: $command->option('info'),
        );
    }
}
