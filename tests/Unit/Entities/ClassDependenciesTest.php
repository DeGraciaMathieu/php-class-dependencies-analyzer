<?php

test('it encapsulates the dependencies', function () {

    $classDependencies = $this->oneClassDependencies()
        ->withDependencies([
            'A',
            'B',
            'C',
        ])
        ->build();

    $dependencies = $classDependencies->getDependencies();

    expect($dependencies)->toBe([
        'A',
        'B',
        'C',
    ]);
});

test('it returns the correct FQCN', function () {

    $classDependencies = $this->oneClassDependencies()
        ->withFqcn('A')
        ->build();

    $fqcn = $classDependencies->getName();

    expect($fqcn)->toBe('A');
});

test('it correctly checks if a class is not a dependency', function () {

    $classDependencies = $this->oneClassDependencies()
        ->withFqcn('A')
        ->withDependencies([
            'B',
        ])
        ->build();

    $c = $this->oneClassDependencies()
        ->withFqcn('C')
        ->build();

    expect($classDependencies->hasDependency($c))->toBeFalse();
});

test('it correctly checks if a class is a dependency', function () {

    $classDependencies = $this->oneClassDependencies()
        ->withFqcn('A')
        ->withDependencies([
            'B',
        ])
        ->build();

    $b = $this->oneClassDependencies()
        ->withFqcn('B')
        ->build();

    expect($classDependencies->hasDependency($b))->toBeTrue();
});

test('it calculates the abstractness correctly', function () {

    $classDependencies = $this->oneClassDependencies()
        ->withFqcn('A')
        ->withDependencies([
            'B',
            'C',
            'D',
        ])
        ->build();

    $classDependencies->incrementNumberOfAbstractDependencies();
    $classDependencies->incrementNumberOfAbstractDependencies();

    $classDependencies->calculateAbstractness();

    $abstractness = $classDependencies->toArray()['abstractness'];

    expect($abstractness)->toBe([
        'ratio' => 0.67,
        'numberOfAbstractDependencies' => 2,
    ]);
});
