<?php

use App\Core\Config\Config;
use App\Core\Rules\Rules;

return Config::make()
    ->paths(['app'])
    ->components([
        'Core\Parser' => 'App\Core\Parser\*',
        'Core\Graph' => 'App\Core\Graph\*',
        'Core\Analysis' => 'App\Core\Analysis\*',
        'Core\Report' => 'App\Core\Report\*',
        'Core\Config' => 'App\Core\Config\*',
        'Core\Rendering' => 'App\Core\Rendering\*',
        'Core\Rules' => 'App\Core\Rules\*',
        'Core\Cache' => 'App\Core\Cache\*',
        'Commands' => 'App\Commands\*',
        'Providers' => 'App\Providers\*',
    ])
    ->rules([
        Rules::noCycles(),
        Rules::stableDependencies(),
    ]);
