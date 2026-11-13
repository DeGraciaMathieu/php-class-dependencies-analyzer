<?php

use App\Core\Config\Config;

return Config::make()
    ->paths(['src'])
    ->ignore(['App\Tests\*'])
    ->componentDepth(3);
