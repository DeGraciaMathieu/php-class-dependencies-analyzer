<?php

namespace App\Commands\Analyze;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Filters\Contracts\Transformer;
use App\Presenter\Analyze\Filters\Transformers\NullTransformer;
use App\Presenter\Analyze\Filters\Transformers\TargetTransformer;

class TransformerFactory
{
    public static function make(Command $command): Transformer
    {
        return match ($command->option('target')) {
            default => self::makeTargetTransformer($command),
            null => self::makeNullTransformer(),
        };
    }

    private static function makeTargetTransformer(Command $command): Transformer
    {
        return app(TargetTransformer::class, [
            'target' => $command->option('target'),
            'depthLimit' => $command->option('depth-limit'),
        ]);
    }

    private static function makeNullTransformer(): Transformer
    {
        return app(NullTransformer::class);
    }
}
