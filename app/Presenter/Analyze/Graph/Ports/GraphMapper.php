<?php

namespace App\Presenter\Analyze\Graph\Ports;

use App\Presenter\Analyze\Graph\Ports\Graph;

interface GraphMapper
{
    public function make(array $metrics): Graph;
}
