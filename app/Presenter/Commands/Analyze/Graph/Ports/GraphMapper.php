<?php

namespace App\Presenter\Commands\Analyze\Graph\Ports;

use App\Presenter\Commands\Analyze\Graph\Ports\Graph;

interface GraphMapper
{
    public function make(array $metrics): Graph;
}
