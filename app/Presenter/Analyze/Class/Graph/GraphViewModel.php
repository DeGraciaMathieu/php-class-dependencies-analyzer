<?php

namespace App\Presenter\Analyze\Class\Graph;

use App\Presenter\Analyze\Class\Graph\GraphEnums;
use App\Presenter\Analyze\Shared\Network\Network;

class GraphViewModel
{
    public function __construct(
        public readonly Network $network,
    ) {}

    public function hasManyNodes(): bool
    {
        return $this->network->countNodes() > GraphEnums::READABILITY_THRESHOLD->value;
    }

    public function nodes(): array
    {
        return $this->network->nodes();
    }

    public function edges(): array
    {
        return $this->network->edges();
    }
}
