<?php

use App\Core\Analysis\ComponentAnalyzer;
use App\Core\Analysis\ComponentResolver;
use App\Core\Analysis\SdpAnalyzer;
use App\Core\Analysis\SdpViolation;
use App\Core\Graph\GraphBuilder;
use App\Core\Parser\ClassKind;
use App\Core\Parser\ParsedClass;

/**
 * @param  array<string, list<string>>  $dependencies
 * @return list<SdpViolation>
 */
function analyzeSdp(array $dependencies): array
{
    $classes = [];

    foreach ($dependencies as $fqcn => $targets) {
        $classes[] = new ParsedClass($fqcn, ClassKind::Class_, $targets, 'file.php');
    }

    $graph = (new GraphBuilder)->build($classes);

    return (new SdpAnalyzer(new ComponentAnalyzer(new ComponentResolver)))->analyze($graph);
}

it('reports a stable component depending on an unstable one', function () {
    $violations = analyzeSdp([
        'App\Stable\One' => ['App\Unstable\One'],
        'App\Client\One' => ['App\Stable\One'],
        'App\Client\Two' => ['App\Stable\One'],
        'App\Client\Three' => ['App\Stable\One'],
        'App\Client\Four' => ['App\Stable\One'],
        'App\Unstable\One' => ['App\Leaf\One', 'App\Leaf\Two', 'App\Leaf\Three', 'App\Leaf\Four'],
        'App\Leaf\One' => [],
        'App\Leaf\Two' => [],
        'App\Leaf\Three' => [],
        'App\Leaf\Four' => [],
    ]);

    expect($violations)->toHaveCount(1)
        ->and($violations[0]->from)->toBe('App\Stable')
        ->and($violations[0]->fromInstability)->toEqualWithDelta(0.2, 1e-12)
        ->and($violations[0]->to)->toBe('App\Unstable')
        ->and($violations[0]->toInstability)->toEqualWithDelta(0.8, 1e-12)
        ->and($violations[0]->delta)->toEqualWithDelta(0.6, 1e-12);
});

it('reports nothing when depending on a more stable component', function () {
    $violations = analyzeSdp([
        'App\Unstable\One' => ['App\Stable\One'],
        'App\Stable\One' => [],
    ]);

    expect($violations)->toBe([]);
});

it('reports nothing when instabilities are equal', function () {
    $violations = analyzeSdp([
        'App\A\One' => ['App\B\One'],
        'App\B\One' => ['App\A\One'],
    ]);

    expect($violations)->toBe([]);
});

it('sorts violations by delta descending then by source component', function () {
    $violations = analyzeSdp([
        'App\A\One' => ['App\T\One'],
        'App\B\One' => ['App\T\One'],
        'App\C\One' => ['App\T\One'],
        'App\W\A' => ['App\A\One'],
        'App\W\B1' => ['App\B\One'],
        'App\W\B2' => ['App\B\One'],
        'App\W\B3' => ['App\B\One'],
        'App\W\C' => ['App\C\One'],
        'App\T\One' => array_map(static fn (int $i): string => "App\\Z\\Leaf{$i}", range(1, 9)),
        ...array_fill_keys(array_map(static fn (int $i): string => "App\\Z\\Leaf{$i}", range(1, 9)), []),
    ]);

    $pairs = array_map(static fn (SdpViolation $violation): array => [$violation->from, $violation->to, $violation->delta], $violations);

    expect($pairs)->toBe([
        ['App\B', 'App\T', 0.5],
        ['App\A', 'App\T', 0.25],
        ['App\C', 'App\T', 0.25],
    ]);
});
