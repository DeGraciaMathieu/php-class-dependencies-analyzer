<?php

namespace App\Application\Cyclic;

use App\Domain\Services\Cycle;
use App\Application\Cyclic\CyclicResponse;

class CyclicResponseMapper
{
    public function from(Cycle $cycles): CyclicResponse
    {
        return new CyclicResponse(
            count: $cycles->count(),
            cycles: $cycles->all(),
        );
    }
}
