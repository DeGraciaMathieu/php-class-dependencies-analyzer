<?php

use App\Presenter\NameFormatter;

test('it formats class names', function () {
    expect(NameFormatter::className('App\Domain\Services\CyclicDependency'))->toBe('CyclicDependency');
});

test('it formats human readable names', function () {
    expect(NameFormatter::humanReadable('App\Domain\Services\CyclicDependency'))->toBe('Services\\CyclicDependency');
});

test('it keeps short names', function () {
    expect(NameFormatter::humanReadable('App\Foo'))->toBe('App\Foo');
});
