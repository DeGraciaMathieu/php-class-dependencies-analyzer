<?php

use App\Presenter\Analyze\Shared\Filters\Collectors\Depth;
use App\Presenter\Analyze\Shared\Filters\Collectors\Metrics;
use App\Presenter\Analyze\Shared\Filters\Transformers\TargetTransformer;

it('should transform the target', function () {

    $filter = new TargetTransformer(
        new Depth(),
        new Metrics(),
        'A',
    );

    $result = $filter->apply([
        'A' => $this->oneAnalyzeMetric()->withName('A')->withDependencies(['B'])->build(),
        'B' => $this->oneAnalyzeMetric()->withName('B')->build(),
        'C' => $this->oneAnalyzeMetric()->withName('C')->build(),
    ]);

    expect($result)->toHaveLength(2);
    expect($result)->toHaveKeys(['A', 'B']);
});

it('should throw an exception if the target is not found', function () {
    
    $filter = new TargetTransformer(
        new Depth(),
        new Metrics(),
        'D',
    );  

    $filter->apply([]);

})->throws(Exception::class, 'Target D not found on metrics, try verify the target name.');

it('should stop if the depth limit is reached', function () {

    $filter = new TargetTransformer(
        new Depth(),
        new Metrics(),
        'A',
        3,
    );

    $result = $filter->apply([
        'A' => $this->oneAnalyzeMetric()->withName('A')->withDependencies(['B'])->build(),
        'B' => $this->oneAnalyzeMetric()->withName('B')->withDependencies(['C'])->build(),
        'C' => $this->oneAnalyzeMetric()->withName('C')->withDependencies(['D'])->build(),
        'D' => $this->oneAnalyzeMetric()->withName('D')->build(),
    ]);

    expect($result)->toHaveLength(3);
    expect($result)->toHaveKeys(['A', 'B', 'C']);
});
