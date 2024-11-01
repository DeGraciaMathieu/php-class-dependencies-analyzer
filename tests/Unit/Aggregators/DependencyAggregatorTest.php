<?php

test('it calculates the instability correctly', function () {

    $dependencyAggregator = $this->oneDependencyAggregator()
        ->withManyClassDependencies([   
            $this->oneClassDependencies()->withFqcn('A')->withDependencies(['B', 'C'])->build(),
            $this->oneClassDependencies()->withFqcn('C')->withDependencies(['D'])->build(),
        ])
        ->build();

    $dependencyAggregator->calculateClassesInstability();

    $metrics = $dependencyAggregator->toArray();

    expect($metrics['A'])->toMatchArray([
        'name' => 'A',
        'instability' => 1.0,
    ]);

    expect($metrics['C'])->toMatchArray([
        'name' => 'C',
        'instability' => 0.5,
    ]);
});

test('it keeps only classes by given filters', function () {

    $dependencyAggregator = $this->oneDependencyAggregator()
        ->withManyClassDependencies([
            $this->oneClassDependencies()->withFqcn('App\Domain\Aggregators\DependencyAggregator')->build(),
        ])
        ->build();

    $dependencyAggregator->filterClasses(only: ['Domain']);

    $dependencies = $dependencyAggregator->toArray();

    expect($dependencies)->toHaveLength(1);
    expect($dependencies)->toHaveKey('App\Domain\Aggregators\DependencyAggregator');
});

test('it filters classes by given exclude filters', function () {

    $dependencyAggregator = $this->oneDependencyAggregator()
        ->withManyClassDependencies([
            $this->oneClassDependencies()->withFqcn('App\Application\Analyze\AnalyzeAction')->build(),
        ])
        ->build();

    $dependencyAggregator->filterClasses(exclude: ['Application']);

    $dependencies = $dependencyAggregator->toArray();

    expect($dependencies)->toHaveLength(0);
});
