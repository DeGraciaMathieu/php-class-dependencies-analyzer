<?php

use App\Core\Config\Config;
use App\Core\Rules\Rules;

return Config::make()
    ->paths(['tests/Fixtures/Core/Project'])
    ->rules([
        Rules::noCycles(),
    ])
    ->baseline('tests/Fixtures/Core/Config/baseline.json');
