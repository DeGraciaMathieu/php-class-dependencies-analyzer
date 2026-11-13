<?php

use App\Core\Analysis\StronglyConnectedComponents;

it('finds two disjoint cycles', function () {
    $components = StronglyConnectedComponents::find([
        'A' => ['B'],
        'B' => ['A'],
        'C' => ['D'],
        'D' => ['E'],
        'E' => ['C'],
    ]);

    expect($components)->toBe([['C', 'D', 'E'], ['A', 'B']]);
});

it('merges nested cycles into a single component', function () {
    $components = StronglyConnectedComponents::find([
        'A' => ['B'],
        'B' => ['C', 'D'],
        'C' => ['A'],
        'D' => ['B'],
    ]);

    expect($components)->toBe([['A', 'B', 'C', 'D']]);
});

it('returns only singletons for a DAG', function () {
    $components = StronglyConnectedComponents::find([
        'C' => [],
        'A' => ['B', 'C'],
        'B' => ['C'],
    ]);

    expect($components)->toBe([['A'], ['B'], ['C']]);
});

it('ignores targets missing from the adjacency keys', function () {
    $components = StronglyConnectedComponents::find([
        'A' => ['B', 'Unknown'],
        'B' => ['A', 'Unknown'],
    ]);

    expect($components)->toBe([['A', 'B']]);
});

it('sorts members and components deterministically', function () {
    $components = StronglyConnectedComponents::find([
        'Z' => ['Y'],
        'Y' => ['Z'],
        'M' => [],
        'C' => ['B'],
        'B' => ['C'],
    ]);

    expect($components)->toBe([['B', 'C'], ['Y', 'Z'], ['M']]);
});

it('handles a 50 000 node linear chain without recursion', function () {
    $size = 50_000;
    $adjacency = [];

    for ($i = 0; $i < $size; $i++) {
        $adjacency["N{$i}"] = $i + 1 < $size ? ['N'.($i + 1)] : [];
    }

    $components = StronglyConnectedComponents::find($adjacency);

    expect($components)->toHaveCount($size)
        ->and(array_filter($components, static fn (array $members): bool => count($members) !== 1))->toBe([]);
});

it('handles a 50 000 node ring as a single component', function () {
    $size = 50_000;
    $adjacency = [];

    for ($i = 0; $i < $size; $i++) {
        $adjacency["N{$i}"] = ['N'.(($i + 1) % $size)];
    }

    $components = StronglyConnectedComponents::find($adjacency);

    expect($components)->toHaveCount(1)
        ->and($components[0])->toHaveCount($size);
});
