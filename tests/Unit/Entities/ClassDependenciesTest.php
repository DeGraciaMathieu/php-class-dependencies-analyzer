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

test('it calculates the abstractness correctly', function (int $numberOfAbstractDependencies, float $expected) {

    $classDependencies = $this->oneClassDependencies()
        ->withFqcn('A')
        ->build();

    for ($i = 0; $i < $numberOfAbstractDependencies; $i++) {
        $classDependencies->incrementNumberOfAbstractDependencies();
    }

    $classDependencies->calculateAbstractness();

    expect($classDependencies->getAbstractness())->toBe($expected);

})->with([
    [0, 0.0],
    [1, 0.5],
    [2, 1.0],
]);

test('it calculates the abstractness correctly when there are no dependencies', function () {

    $classDependencies = $this->oneClassDependencies()->build();

    $classDependencies->calculateAbstractness();

    expect($classDependencies->getAbstractness())->toBe(0.0);
});
