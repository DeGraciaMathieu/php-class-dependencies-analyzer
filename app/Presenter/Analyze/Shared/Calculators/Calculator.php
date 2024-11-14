<?php

namespace App\Presenter\Analyze\Shared\Calculators;

use App\Application\Analyze\AnalyzeMetric;

interface Calculator
{
    public static function calculate(AnalyzeMetric $metric): string;
}
