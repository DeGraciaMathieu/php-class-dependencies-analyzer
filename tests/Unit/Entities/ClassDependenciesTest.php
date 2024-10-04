<?php

test('it calculates the instability correctly', function () {

    $classDependencies = $this->oneClassDependencies()->build();

    $roundedInstability = $classDependencies->getRoundedInstability();

    expect($roundedInstability)->toBe(0.0);
});

test('it encapsulates the dependencies', function () {

    $classDependencies = $this->oneClassDependencies()->build();

    $dependencies = $classDependencies->getDependencies();

    expect($dependencies)->toBe([
        'App\Domain\ValueObjects\Name',
        'App\Domain\ValueObjects\Email',
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