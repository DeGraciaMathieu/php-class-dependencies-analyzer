<?php

namespace App\Presenter\Analyze\Filters\Transformers;

use App\Presenter\Analyze\Filters\Contracts\Transformer;

class NullTransformer implements Transformer
{
    public function apply(array $metrics): array
    {
        return $metrics;
    }
}
