<?php

namespace App\Presenter\Commands\Analyze\Filters;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Commands\Analyze\Filters\Filter;
use App\Presenter\Commands\Analyze\Filters\NullFilter;
use App\Presenter\Commands\Analyze\Filters\TargetFilter;

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
