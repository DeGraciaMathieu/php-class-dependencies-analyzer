<?php

use App\Presenter\Analyze\Component\Shared\Component;
use App\Presenter\Analyze\Component\Shared\ComponentMapper;

it('should map metrics to components', function () {
    
    $mapper = new ComponentMapper();

    $metrics = [
        'A' => [
            $this->oneAnalyzeMetric()->withName('A\Class1')->build(),
            $this->oneAnalyzeMetric()->withName('A\Class2')->build(),
            $this->oneAnalyzeMetric()->withName('A\Class3')->build(),
        ]
    ];

    $components = $mapper->from($metrics);

    expect($components)->toHaveCount(1);
    expect($components[0])->toBeInstanceOf(Component::class);
    expect($components[0]->name())->toBe('A');
});

it('should map metrics to components with dependencies', function () {

    $mapper = new ComponentMapper();

    $metrics = [
        'A' => [
            $this->oneAnalyzeMetric()->withName('A\Class1')->withDependencies(['B\Class2'])->build(),
        ], 
        'B' => [
            //
        ]
    ];

    $components = $mapper->from($metrics);

    expect($components)->toHaveCount(2);
    expect($components[0]->dependencies())->toBe(['B']);

});

it('should not keep dependencies from unwanted namespaces', function () {

    $mapper = new ComponentMapper();

    $metrics = [
        'A' => [
            /**
             * This dependency is in an unwanted namespace C
             */
            $this->oneAnalyzeMetric()->withName('A\Class1')->withDependencies(['C\Class2'])->build(),
        ],
        'B' => [
            //
        ]
    ];

    $components = $mapper->from($metrics);

    expect($components)->toHaveCount(2);
    expect($components[0]->dependencies())->toBe([]);
});

it('should calculate the average abstractness', function () {

    $mapper = new ComponentMapper();

    $metrics = [
        'A' => [
            $this->oneAnalyzeMetric()->build(),
            $this->oneAnalyzeMetric()->isAbstract()->build(),
            $this->oneAnalyzeMetric()->isAbstract()->build(),
            $this->oneAnalyzeMetric()->isAbstract()->build(),
        ],
    ];

    $components = $mapper->from($metrics);

    expect($components[0]->countClasses())->toBe(4);
    expect($components[0]->countAbstractions())->toBe(3);
    expect($components[0]->abstractness())->toBe(0.75);
});

it('should calculate the average instability', function () {

    $mapper = new ComponentMapper();

    $metrics = [
        'A' => [
            $this->oneAnalyzeMetric()->withInstability(0.3)->build(),
            $this->oneAnalyzeMetric()->withInstability(0.7)->build(),
            $this->oneAnalyzeMetric()->withInstability(1)->build(),
        ],
    ];

    $components = $mapper->from($metrics);

    expect($components[0]->instability())->toBe(0.67);
});
