<?php

use App\Domain\ValueObjects\Fqcn;

test('it able to identify himself', function () {

    $fqcn = new Fqcn('App\Domain\ValueObjects\Coupling');

    $looked = $fqcn->looksLike([
        'App\Domain\ValueObjects\Coupling',
        'App\Domain\ValueObjects\Instability',
    ]);

    expect($looked)->toBeTrue();
});

test('it able of not identifying himself', function () {

    $fqcn = new Fqcn('App\Domain\ValueObjects\Coupling');

    $looked = $fqcn->looksLike([
        'App\Domain\ValueObjects\Other',
    ]);

    expect($looked)->toBeFalse();
});
