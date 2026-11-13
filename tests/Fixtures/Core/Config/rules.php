<?php

use App\Core\Config\Config;
use App\Core\Rules\Rules;

return Config::make()
    ->paths(['tests/Fixtures/Core/Project'])
    ->rules([
        Rules::noCycles(),
        Rules::stableDependencies(),
        Rules::maxDistanceFromMainSequence(0.5)->for('Shop\Catalog'),
    ]);
