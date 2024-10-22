<?php

namespace App\Application\Cyclic;

use App\Application\Cyclic\CyclicResponse;

class CyclicResponseMapper
{
    public function from(array $cycles): CyclicResponse
    {
        return new CyclicResponse(
            count: count($cycles),
            cycles: $cycles,
        );
    }
}
