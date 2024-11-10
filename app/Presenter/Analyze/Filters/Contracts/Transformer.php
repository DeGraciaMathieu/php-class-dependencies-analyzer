<?php

namespace App\Presenter\Analyze\Filters\Contracts;

interface Transformer
{
    public function apply(array $metrics): array;
}
