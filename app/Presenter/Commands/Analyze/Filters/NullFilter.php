<?php

namespace App\Presenter\Commands\Analyze\Filters;

use App\Presenter\Commands\Analyze\Filters\Filter;

class NullFilter implements Filter
{
    public function apply(array $metrics): array
    {
        return $metrics;
    }
}
