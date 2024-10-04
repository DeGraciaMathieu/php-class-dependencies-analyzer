<?php

test('it calculates the instability correctly', function () {

    $dependencyAggregator = $this->oneDependencyAggregator()
        ->addClassDependencies(
            $this->oneClassDependencies()->withFqcn('A')->withDependencies(['B', 'C'])->build()
        )
        ->addClassDependencies(
            $this->oneClassDependencies()->withFqcn('C')->withDependencies(['D'])->build()
        )
        ->build();

    $dependencyAggregator->calculateClassesInstability();

    $metrics = $dependencyAggregator->toArray();

    expect($metrics[0])->toMatchArray([
        'name' => 'A',
        'instability' => 1.0,
    ]);

    expect($metrics[1])->toMatchArray([
        'name' => 'C',
        'instability' => 0.5,
    ]);
});