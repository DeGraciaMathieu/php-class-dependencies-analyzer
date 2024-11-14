<?php

namespace App\Presenter\Analyze\Class\Summary;

use App\Application\Analyze\AnalyzeMetric;
use App\Presenter\Analyze\Shared\Calculators\StabilityCalculator;
use App\Presenter\Analyze\Shared\Calculators\AbstractnessCalculator;
use App\Presenter\Analyze\Shared\Calculators\MaintainabilityCalculator;

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
        return array_map(function (AnalyzeMetric $metric) {
            return [
                'name' => $metric->name(),
                'Ec' => $metric->efferentCoupling(),
                'Ac' => $metric->afferentCoupling(),
                'I' => $metric->instability(),
                'Na' => $metric->numberOfAbstractDependencies(),
                'A' => $metric->abstractnessRatio(),
            ];
        }, $metrics);
    }

    private static function formatHumanReadableMetrics(array $metrics): array
    {
        return array_map(function ($metric) {
            return [
                'name' => $metric->name(),
                'stability' => StabilityCalculator::calculate($metric),
                'abstractness' => AbstractnessCalculator::calculate($metric),
                'maintainability' => MaintainabilityCalculator::calculate($metric),
            ];
        }, $metrics);
    }
}
