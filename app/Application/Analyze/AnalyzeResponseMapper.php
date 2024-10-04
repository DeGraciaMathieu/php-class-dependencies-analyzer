<?php

namespace App\Application\Analyze;

use App\Application\Analyze\AnalyzeResponse;
use App\Domain\Aggregators\DependencyAggregator;

class AnalyzeResponseMapper
{
    public function from(DependencyAggregator $dependencyAggregator): AnalyzeResponse
    {
        return new AnalyzeResponse(
            count: $dependencyAggregator->count(),
            metrics: $dependencyAggregator->toArray(),
        );
    }
}
