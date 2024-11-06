<?php

namespace App\Presenter\Cyclic\Summary;

use App\Presenter\Cyclic\Summary\CycleHelper;

class CyclicPresenterMapper
{
    public function from(array $cycles): array
    {
        return array_map(function (array $cycle) {
            return [CycleHelper::through($cycle)];
        }, $cycles);
    }
}
