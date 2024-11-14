<?php

namespace App\Presenter\Analyze\Shared\Filters\Transformers;

use App\Presenter\Analyze\Shared\Filters\Contracts\Transformer;

class NullTransformer implements Transformer
{
    public function apply(array $metrics): array
    {
        return $metrics;
    }
}
