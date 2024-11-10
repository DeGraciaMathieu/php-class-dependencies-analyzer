<?php

use App\Presenter\Analyze\Filters\Collectors\Depth;
use App\Presenter\Analyze\Filters\Collectors\Metrics;
use App\Presenter\Analyze\Filters\Transformers\TargetTransformer;

it('should transform the target', function () {

    $filter = new TargetTransformer(
        new Depth(),
        new Metrics(),
        'A',
    );

    $result = $filter->apply([
        'A' => [
            'name' => 'A',
            'dependencies' => ['B'],
        ],
        'B' => [
            'name' => 'B',
            'dependencies' => [],
        ],
        'C' => [
            'name' => 'C',
            'dependencies' => [],
        ],
    ]);

    expect($result)->toBe([
        'A' => [
            'name' => 'A',
            'dependencies' => ['B'],
        ],
        'B' => [
            'name' => 'B',
            'dependencies' => [],
        ],
    ]);
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
        1,
    );

    $result = $filter->apply([
        'A' => [
            'name' => 'A',
            'dependencies' => ['B'],
        ],
        'B' => [
            'name' => 'B',
            'dependencies' => ['C'],
        ],
        'C' => [
            'name' => 'C',
            'dependencies' => [],
        ],
    ]);

    expect($result)->toBe([
        'A' => [
            'name' => 'A',
            'dependencies' => ['B'],
        ],
    ]);
});
