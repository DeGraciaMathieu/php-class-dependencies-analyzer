<?php

namespace App\Presenter\Analyze\Shared\Network;

use App\Presenter\Analyze\Shared\Network\Networkable;
use App\Presenter\Analyze\Shared\Network\NetworkAttribute;

class NetworkAttributesMapper
{
    public function map(array $items): array
    {   
        $attributes = [];

        foreach ($items as $item) {
            $attributes[] = $this->makeNetworkAttribute($item);
        }

        return $attributes;
    }

    public function makeNetworkAttribute(Networkable $item): NetworkAttribute
    {
        return new NetworkAttribute(
            $item->name(),
            $item->instability(),
            $item->dependencies(),
        );
    }
}
