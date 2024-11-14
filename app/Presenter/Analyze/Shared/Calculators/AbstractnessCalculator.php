<?php

namespace App\Presenter\Analyze\Shared\Calculators;

use App\Application\Analyze\AnalyzeMetric;
use App\Presenter\Analyze\Shared\Calculators\Calculator;

class AbstractnessCalculator implements Calculator
{
    public static function calculate(AnalyzeMetric $metric): string
    {
        $ratio = $metric->abstractnessRatio();

        if ($ratio > 0.7) {
            return 'abstract';
        }

        if ($ratio < 0.3) {
            return 'concrete';
        }

        return 'balanced';
    }
}
