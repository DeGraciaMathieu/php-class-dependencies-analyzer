<?php

use App\Domain\Services\Instability;
use App\Domain\ValueObjects\Coupling;

test('it calculates the instability', function (int $afferent, int $efferent, float $expected) {

    $instability = Instability::calculate(
        Coupling::from($afferent), 
        Coupling::from($efferent),
    );

    expect($instability)->toBe($expected);

})->with([
    [1, 1, 0.5],
    [1, 2, 0.3333333333333333],
    [2, 1, 0.6666666666666666],
    [0, 0, 0.0],
]);
