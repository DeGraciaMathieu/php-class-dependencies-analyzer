<?php

namespace App\Presenter\Analyze\Summary\Calculators;

use App\Presenter\Analyze\Summary\Calculators\Calculator;

class MaintainabilityCalculator implements Calculator
{
    public static function calculate(array $metric): string
    {
        [$abstractness, $instability] = self::getValues($metric);

        /**
         * lowly abstract and highly unstable: will suffer from its dependencies
         */
        if (self::isLowlyAbstractAndHighlyUnstable($abstractness, $instability)) {
            return 'suffering';
        }

        /**
         * lowly abstract and stable: a modification will impact many classes
         */
        if (self::isLowlyAbstractAndHighlyStable($abstractness, $instability)) {
            return 'risky';
        }

        return 'good';
    }

    private static function isLowlyAbstractAndHighlyUnstable(float $abstractness, float $instability): bool
    {
        return $abstractness < 0.3 && $instability > 0.7;
    }

    private static function isLowlyAbstractAndHighlyStable(float $abstractness, float $instability): bool
    {
        return $abstractness < 0.3 && $instability < 0.3;
    }

    private static function getValues(array $metric): array
    {
        return [
            $metric['abstractness']['ratio'],
            $metric['coupling']['instability'],
        ];
    }
}
