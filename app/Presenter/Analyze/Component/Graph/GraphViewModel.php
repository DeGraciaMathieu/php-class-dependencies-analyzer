<?php

namespace App\Presenter\Analyze\Component\Graph;

use App\Presenter\Analyze\Shared\Network\Network;

class GraphViewModel
{
    public function __construct(
        public readonly Network $network,
    ) {}

    public function nodes(): array
    {
        return $this->network->nodes();
    }

    public function edges(): array
    {
        return $this->network->edges();
    }
}
