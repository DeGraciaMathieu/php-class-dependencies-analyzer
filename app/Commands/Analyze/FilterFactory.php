<?php

namespace App\Commands\Analyze;

use App\Presenter\Analyze\Filters\Filter;
use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Filters\NullFilter;
use App\Presenter\Analyze\Filters\TargetFilter;

class FilterFactory
{
    public static function make(Command $command): Filter
    {
        return match ($command->option('target')) {
            default => self::makeTargetFilter($command),
            null => app(NullFilter::class),
        };
    }

    private static function makeTargetFilter(Command $command): TargetFilter
    {
        return app(TargetFilter::class, [
            'target' => $command->option('target'),
        ]);
    }
}
