<?php

use App\Core\Config\Config;

/*
 * The v1 core is split into its modules, the legacy layers
 * (App\Application, App\Domain, ...) resolve by namespace depth.
 */
return Config::make()
    ->paths(['app'])
    ->components([
        'Core\Parser' => 'App\Core\Parser\*',
        'Core\Graph' => 'App\Core\Graph\*',
        'Core\Analysis' => 'App\Core\Analysis\*',
        'Core\Report' => 'App\Core\Report\*',
        'Core\Config' => 'App\Core\Config\*',
        'Core\Rendering' => 'App\Core\Rendering\*',
    ]);
