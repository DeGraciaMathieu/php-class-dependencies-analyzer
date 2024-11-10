<?php

namespace App\Presenter\Analyze\Summary\Calculators;

interface Calculator
{
    public static function calculate(array $metric): string;
}
