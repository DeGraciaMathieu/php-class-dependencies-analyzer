<?php

namespace App\Presenter\Analyze\Shared\Calculators;

use App\Application\Analyze\AnalyzeMetric;
use App\Presenter\Analyze\Shared\Calculators\Calculator;

class MaintainabilityCalculator implements Calculator
{
    public static function calculate(AnalyzeMetric $metric): string
    {
        /**
         * lowly abstract and highly unstable: will suffer from its dependencies
         */
        if (self::isLowlyAbstractAndHighlyUnstable($metric)) {
            return 'suffering';
        }

        /**
         * lowly abstract and stable: a modification will impact many classes
         */
        if (self::isLowlyAbstractAndHighlyStable($metric)) {
            return 'risky';
        }

        return 'good';
    }

    private static function isLowlyAbstractAndHighlyUnstable(AnalyzeMetric $metric): bool
    {
        return $metric->abstractnessRatio() < 0.3 && $metric->instability() > 0.7;
    }

    private static function isLowlyAbstractAndHighlyStable(AnalyzeMetric $metric): bool
    {
        return $metric->abstractnessRatio() < 0.3 && $metric->instability() < 0.3;
    }
}
