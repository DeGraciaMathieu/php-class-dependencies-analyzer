<?php

use App\Presenter\Analyze\Shared\Calculators\MaintainabilityCalculator;

it('calculates the maintainability of a class', function (float $abstractness, float $instability, string $expected) {

    $calculator = new MaintainabilityCalculator();

    $analyzeMetric = $this->oneAnalyzeMetric()
        ->withInstability($instability)
        ->withAbstractnessRatio($abstractness)
        ->build();

    $maintainability = $calculator->calculate($analyzeMetric);

    expect($maintainability)->toBe($expected);

})->with([
    [0, 0, 'risky'], // lowly abstract and highly unstable
    [1, 1, 'good'], // highly abstract and highly unstable
    [0, 1, 'suffering'], // lowly abstract and highly unstable
    [1, 0, 'good'], // highly abstract and stable
]);
