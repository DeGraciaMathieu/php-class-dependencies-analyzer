<?php

namespace App\Presenter\Analyze\Class\Shared;

use App\Application\Analyze\AnalyzeMetric;
    
/**
 * @todo : its work but it's not efficient
 */
class MetricMapper
{
    public function from(array $metrics): array
    {
        $class = [];

        foreach ($metrics as $metric) {
            $class[] = $this->makeClass($metric);
        }

        return $class;
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
