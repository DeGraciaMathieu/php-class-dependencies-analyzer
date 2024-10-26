<?php

namespace App\Presenter\Commands\Analyze\Graph;

use App\Presenter\Commands\Analyze\Graph\Ports\Graph;

class GraphViewModel
{
    public function __construct(
        public readonly Graph $graph,
    ) {}
}
