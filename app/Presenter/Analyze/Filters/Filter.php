<?php

namespace App\Presenter\Analyze\Filters;

interface Filter
{
    public function apply(array $metrics): array;
}
