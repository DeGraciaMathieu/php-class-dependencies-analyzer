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
            metrics: $this->map($dependencyAggregator),
        );
    }

    private function map(DependencyAggregator $dependencyAggregator): array
    {
        return array_map(function (array $metric) {
            return new AnalyzeMetric($metric);
        }, $dependencyAggregator->toArray());
    }
}   
