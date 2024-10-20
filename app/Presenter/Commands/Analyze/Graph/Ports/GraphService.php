<?php

namespace App\Presenter\Commands\Analyze\Graph\Ports;

interface GraphService
{
    public function generate(array $metrics): string;
}
