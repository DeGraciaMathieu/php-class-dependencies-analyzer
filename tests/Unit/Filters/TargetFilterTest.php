<?php

use App\Presenter\Analyze\Filters\Depth;
use App\Presenter\Analyze\Filters\Metrics;
use App\Presenter\Analyze\Filters\TargetFilter;

it('should filter the target', function () {

    $filter = new TargetFilter(
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
    
    $filter = new TargetFilter(
        new Depth(),
        new Metrics(),
        'D',
    );  

    $filter->apply([]);

})->throws(Exception::class, 'Target not found on metrics, try verify the target name.');
