<?php

namespace App\Presenter\Analyze\Filters;

use App\Presenter\Analyze\Filters\Filter;

class NullFilter implements Filter
{
    public function apply(array $metrics): array
    {
        return $metrics;
    }
}
