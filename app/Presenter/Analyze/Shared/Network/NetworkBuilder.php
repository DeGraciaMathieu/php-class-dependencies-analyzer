<?php

namespace App\Presenter\Analyze\Shared\Network;

use App\Presenter\Analyze\Shared\Network\Network;
use App\Presenter\Analyze\Shared\Network\NetworkAttribute;

interface NetworkBuilder
{
    /**
     * @param array<NetworkAttribute> $attributes
     */
    public function build(array $attributes): Network;
}
