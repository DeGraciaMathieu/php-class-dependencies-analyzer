<?php

use App\Domain\Services\CyclicDependency;

test('it detects cyclic dependencies', function () {

    $dependencyAggregator = $this->oneDependencyAggregator()
        ->withManyClassDependencies([
            $this->oneClassDependencies()->withFqcn('A')->withDependencies(['B'])->build(),
            $this->oneClassDependencies()->withFqcn('B')->withDependencies(['C'])->build(),
            $this->oneClassDependencies()->withFqcn('C')->withDependencies(['A'])->build(),
        ])
        ->build();

    $cycles = app(CyclicDependency::class)->detect($dependencyAggregator->classes());

    expect($cycles)->toBe([
        ['A', 'B', 'C'],
    ]);
});

test('it detects classes with multiple cycles', function () {

    $dependencyAggregator = $this->oneDependencyAggregator()
        ->withManyClassDependencies([
            $this->oneClassDependencies()->withFqcn('A')->withDependencies(['B', 'C'])->build(),
            $this->oneClassDependencies()->withFqcn('B')->withDependencies(['A'])->build(),
            $this->oneClassDependencies()->withFqcn('C')->withDependencies(['A'])->build(),
        ])
        ->build();

    $cycles = app(CyclicDependency::class)->detect($dependencyAggregator->classes());

    expect($cycles)->toBe([
        ['A', 'B'],
        ['A', 'C'],
    ]);
});