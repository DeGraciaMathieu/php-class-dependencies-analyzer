<?php

use App\Domain\ValueObjects\Coupling;

test('it increments the coupling', function () {

    $coupling = new Coupling(afferent: 0, efferent: 0);

    $coupling->incrementAfferent();
    $coupling->incrementAfferent();
    $coupling->incrementAfferent();

    expect($coupling->toArray())->toBe([
        'afferent' => 3,
        'efferent' => 0,
        'instability' => 0.0,
    ]);
});


it('it calculates instability', function (int $afferent, int $efferent, float $instability) {

    $coupling = new Coupling(afferent: $afferent, efferent: $efferent);

    $coupling->calculateInstability();

    expect($coupling->toArray())->toBe([
        'afferent' => $afferent,
        'efferent' => $efferent,
        'instability' => $instability,
    ]);

})->with([
    [0, 0, 0.0],
    [1, 0, 0.0],
    [1, 1, 0.5],
    [2, 1, 0.33],
    [3, 1, 0.25],
    [4, 1, 0.2],
    [5, 1, 0.17],
    [1, 2, 0.67],
    [1, 3, 0.75],
    [1, 4, 0.8],
    [1, 5, 0.83],
]);
