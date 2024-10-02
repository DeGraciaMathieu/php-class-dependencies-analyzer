<?php

namespace App\Application\Analyze;

use App\Domain\Entities\ClassDependencies;
use App\Application\Analyze\AnalyzeResponse;
use App\Domain\Aggregators\DependencyAggregator;

class ResponseMapper
{
    public static function from(DependencyAggregator $dependencyAggregator): AnalyzeResponse
    {
        return new AnalyzeResponse(
            count: $dependencyAggregator->count(),
            metrics: self::mapMetrics($dependencyAggregator),
        );
    }

    private static function mapMetrics(DependencyAggregator $dependencyAggregator)
    {
        return array_map(function (ClassDependencies $class) {
            return $class->toArray();
        }, $dependencyAggregator->classes());
    }
}
