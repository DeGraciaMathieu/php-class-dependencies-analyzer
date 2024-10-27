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

test('it filters classes by given filters', function () {

    $dependencyAggregator = $this->oneDependencyAggregator()
        ->withManyClassDependencies([
            $this->oneClassDependencies()->withFqcn('App\Application\Analyze\AnalyzeAction')->build(),
            $this->oneClassDependencies()->withFqcn('App\Domain\Aggregators\DependencyAggregator')->build(),
            $this->oneClassDependencies()->withFqcn('App\Infrastructure\Presenters\Commands\Analyze\Graph\GraphPresenter')->build(),
        ])
        ->build();

    $dependencyAggregator->keepOnlyClasses(['Domain']);

    $dependencies = $dependencyAggregator->toArray();

    expect($dependencies)->toHaveLength(1);
    expect($dependencies)->toHaveKey('App\Domain\Aggregators\DependencyAggregator');
});
