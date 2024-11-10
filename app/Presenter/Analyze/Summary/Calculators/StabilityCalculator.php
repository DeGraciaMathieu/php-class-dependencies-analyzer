<?php

namespace App\Presenter\Analyze\Summary\Calculators;

use App\Presenter\Analyze\Summary\Calculators\Calculator;

class StabilityCalculator implements Calculator
{
    public static function calculate(array $metric): string
    {
        $instability = $metric['coupling']['instability'];

        if ($instability > 0.7) {
            return 'unstable';
        }

        if ($instability < 0.3) {
            return 'stable';
        }

        return 'flexible';
    }
}
