<?php

namespace App\Presenter\Analyze\Shared\Calculators;

use App\Application\Analyze\AnalyzeMetric;
use App\Presenter\Analyze\Shared\Calculators\Calculator;

class StabilityCalculator implements Calculator
{
    public static function calculate(AnalyzeMetric $metric): string
    {
        $instability = $metric->instability();

        if ($instability > 0.7) {
            return 'unstable';
        }

        if ($instability < 0.3) {
            return 'stable';
        }

        return 'flexible';
    }
}
