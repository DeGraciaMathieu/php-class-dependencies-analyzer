<?php

use App\Presenter\Cyclic\Summary\CycleHelper;

test('it formats a cycle', function () {

    $cycle = ['A', 'B', 'C'];

    $formatted = CycleHelper::through($cycle);

    expect($formatted)->toBe('A -> B -> C -> A');
});

test('it formats a cycle with readable names', function () {

    $cycle = [
        'App\Domain\Services\BarService',
        'App\Domain\Services\FooService',
    ];

    $formatted = CycleHelper::through($cycle);

    expect($formatted)->toBe('\Services\BarService -> \Services\FooService -> \Services\BarService');
});
