<?php

namespace App\Commands\Analyze\Component\Factories;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Shared\Filters\Contracts\Transformer;
use App\Presenter\Analyze\Shared\Filters\Transformers\ComponentTransformer;

class TransformerFactory
{
    public static function make(Command $command): Transformer
    {
        return app(ComponentTransformer::class, [
            'targetedComponents' => $command->argumentToList('components'), 
        ]);
    }
}
