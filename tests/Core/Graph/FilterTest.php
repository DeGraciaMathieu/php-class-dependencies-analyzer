<?php

use App\Core\Graph\Filter;

it('matches everything without globs', function () {
    expect((new Filter)->matches('App\Foo'))->toBeTrue();
});

it('matches exactly without wildcard', function () {
    $filter = new Filter(only: ['App\Foo']);

    expect($filter->matches('App\Foo'))->toBeTrue()
        ->and($filter->matches('App\FooBar'))->toBeFalse()
        ->and($filter->matches('Vendor\App\Foo'))->toBeFalse();
});

it('matches nested namespaces with a wildcard', function () {
    $filter = new Filter(only: ['App\Foo\*']);

    expect($filter->matches('App\Foo\Bar'))->toBeTrue()
        ->and($filter->matches('App\Foo\Bar\Baz'))->toBeTrue()
        ->and($filter->matches('App\Foo'))->toBeFalse()
        ->and($filter->matches('App\FooBar'))->toBeFalse();
});

it('matches a wildcard in the middle', function () {
    $filter = new Filter(only: ['App\*Repository']);

    expect($filter->matches('App\Infrastructure\UserRepository'))->toBeTrue()
        ->and($filter->matches('App\Repository'))->toBeFalse();
});

it('combines only and exclude', function () {
    $filter = new Filter(only: ['App\*'], exclude: ['App\Infrastructure\*']);

    expect($filter->matches('App\Domain\User'))->toBeTrue()
        ->and($filter->matches('App\Infrastructure\UserRepository'))->toBeFalse()
        ->and($filter->matches('Vendor\Package'))->toBeFalse();
});

it('ignores empty globs', function () {
    expect((new Filter(only: [''], exclude: ['']))->matches('App\Foo'))->toBeTrue();
});

it('escapes regex delimiters and metacharacters', function () {
    $filter = new Filter(only: ['App\Fo#o', 'App\B.r']);

    expect($filter->matches('App\Fo#o'))->toBeTrue()
        ->and($filter->matches('App\Bar'))->toBeFalse();
});
