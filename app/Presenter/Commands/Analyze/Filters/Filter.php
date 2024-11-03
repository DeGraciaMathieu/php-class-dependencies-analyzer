<?php

namespace App\Presenter\Commands\Analyze\Filters;

interface Filter
{
    public function apply(array $metrics): array;
}
