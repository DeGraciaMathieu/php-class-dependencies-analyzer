<?php

namespace App\Presenter\Analyze\Summary;

use App\Presenter\Analyze\Summary\Calculators\StabilityCalculator;
use App\Presenter\Analyze\Summary\Calculators\AbstractnessCalculator;
use App\Presenter\Analyze\Summary\Calculators\MaintainabilityCalculator;

class SummaryMapper
{
    public static function from(array $metrics, bool $humanReadable = false): array
    {
        return $humanReadable
            ? self::formatHumanReadableMetrics($metrics)
            : self::formatMetrics($metrics);
    }

    private static function formatMetrics(array $metrics): array
    {
        return array_map(function ($metric) {
            return [
                'name' => $metric['name'],
                'Ec' => $metric['coupling']['efferent'],
                'Ac' => $metric['coupling']['afferent'],
                'I' => $metric['coupling']['instability'],
                'Na' => $metric['abstractness']['numberOfAbstractDependencies'],
                'A' => $metric['abstractness']['ratio'],
            ];
        }, $metrics);
    }

    private static function formatHumanReadableMetrics(array $metrics): array
    {
        return array_map(function ($metric) {
            return [
                'name' => $metric['name'],
                'stability' => StabilityCalculator::calculate($metric),
                'abstractness' => AbstractnessCalculator::calculate($metric),
                'maintainability' => MaintainabilityCalculator::calculate($metric),
            ];
        }, $metrics);
    }
}
