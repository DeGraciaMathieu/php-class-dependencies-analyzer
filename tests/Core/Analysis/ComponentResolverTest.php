<?php

use App\Core\Analysis\ComponentResolver;

it('resolves a class with a named definition', function () {
    $resolver = new ComponentResolver(['Billing' => 'App\Billing\*']);

    expect($resolver->resolve('App\Billing\Invoice'))->toBe('Billing');
});

it('accepts several globs per definition', function () {
    $resolver = new ComponentResolver(['Persistence' => ['App\*Repository', 'App\Database\*']]);

    expect($resolver->resolve('App\Domain\UserRepository'))->toBe('Persistence')
        ->and($resolver->resolve('App\Database\Connection'))->toBe('Persistence');
});

it('gives priority to the first matching definition', function () {
    $resolver = new ComponentResolver([
        'Contracts' => 'App\Billing\Contracts\*',
        'Billing' => 'App\Billing\*',
    ]);

    expect($resolver->resolve('App\Billing\Contracts\Gateway'))->toBe('Contracts')
        ->and($resolver->resolve('App\Billing\Invoice'))->toBe('Billing');
});

it('falls back on the first two namespace segments', function () {
    $resolver = new ComponentResolver(['Billing' => 'App\Billing\*']);

    expect($resolver->resolve('App\Domain\Foo\Bar'))->toBe('App\Domain')
        ->and($resolver->resolve('App\Domain\Bar'))->toBe('App\Domain');
});

it('falls back on the whole namespace when it is shorter than the depth', function () {
    $resolver = new ComponentResolver;

    expect($resolver->resolve('App\Foo'))->toBe('App')
        ->and($resolver->resolve('Foo'))->toBe('\\');
});

it('uses a configurable depth', function () {
    $resolver = new ComponentResolver(depth: 3);

    expect($resolver->resolve('App\Domain\Foo\Bar\Baz'))->toBe('App\Domain\Foo')
        ->and((new ComponentResolver(depth: 1))->resolve('App\Domain\Foo'))->toBe('App');
});

it('rejects a depth lower than one', function () {
    new ComponentResolver(depth: 0);
})->throws(InvalidArgumentException::class);

it('rejects a definition without glob', function (string|array $globs) {
    new ComponentResolver(['Empty' => $globs]);
})->with([[''], [[]], [['']]])->throws(InvalidArgumentException::class);
