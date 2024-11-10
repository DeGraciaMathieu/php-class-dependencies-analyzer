<?php

namespace App\Presenter\Analyze\Summary\Calculators;

use App\Presenter\Analyze\Summary\Calculators\Calculator;

class AbstractnessCalculator implements Calculator
{
    public static function calculate(array $metric): string
    {
        if ($metric['abstractness'] > 0.7) {
            return 'abstract';
        }

        if ($metric['abstractness'] < 0.3) {
            return 'concrete';
        }

        return 'balanced';
    }
}
