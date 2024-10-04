<?php

use App\Domain\ValueObjects\Coupling;

test('it increments the coupling', function () {

    $coupling = new Coupling(1);

    $coupling->increment();

    expect($coupling->getValue())->toBe(2);
});
