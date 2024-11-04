<?php

namespace App\Presenter\Analyze\Graph;

use App\Presenter\Analyze\Graph\GraphEnums;
use App\Presenter\Analyze\Graph\Ports\Graph;

class GraphViewModel
{
    public function __construct(
        public readonly Graph $graph,
    ) {}

    public function hasManyNodes(): bool
    {
        return $this->graph->countNodes() > GraphEnums::READABILITY_THRESHOLD->value;
    }

    public function nodes(): array
    {
        return $this->graph->nodes();
    }

    public function edges(): array
    {
        return $this->graph->edges();
    }
}
