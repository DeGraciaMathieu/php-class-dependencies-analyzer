<?php

use App\Presenter\Commands\Shared\ArrayFormatter;

test('it sorts items by a given key', function () {

    $items = [
        ['foo' => 1],
        ['foo' => 2],
    ];

    $expected = [
        ['foo' => 2],
        ['foo' => 1],
    ];

    expect(ArrayFormatter::sort('foo', $items))->toBe($expected);
});

test('it cuts items', function () {

    $items = [1, 2, 3, 4, 5];

    expect(ArrayFormatter::cut(3, $items))->toBe([1, 2, 3]);
});

test('it keep items when limit is null', function () {

    $items = [1, 2, 3, 4, 5];

    expect(ArrayFormatter::cut(null, $items))->toBe($items);
});

test('it filters items by min value', function () {

    $items = [
        ['foo' => 1],
        ['foo' => 2],
        ['foo' => 3],
    ];

    $expected = [
        ['foo' => 2],
        ['foo' => 3],
    ];

    expect(ArrayFormatter::filterByMinValue('foo', 2, $items))->toBe($expected);
});

test('it keeps items when min value is null', function () {

    $items = [
        ['foo' => 1],
        ['foo' => 2],
        ['foo' => 3],
    ];

    expect(ArrayFormatter::filterByMinValue('foo', null, $items))->toBe($items);
});
