<?php

namespace App\Presenter\Analyze\Class\Shared;

use App\Application\Analyze\AnalyzeMetric;
    
class MetricMapper
{
    public function from(array $metrics): array
    {
        return array_map(function (AnalyzeMetric $metric) {
            return $this->makeClass($metric);
        }, $metrics);
    }

    private function makeClass(AnalyzeMetric $metric): Metric
    {
        return new Metric(
            name: $metric->name(),
            instability: $metric->instability(),
            dependencies: $metric->dependencies(),
        );
    }
}
