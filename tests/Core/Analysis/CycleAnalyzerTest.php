<?php

use App\Core\Analysis\ComponentAnalyzer;
use App\Core\Analysis\ComponentResolver;
use App\Core\Analysis\Cycle;
use App\Core\Analysis\CycleAnalyzer;
use App\Core\Analysis\CycleLevel;
use App\Core\Graph\GraphBuilder;
use App\Core\Parser\ClassKind;
use App\Core\Parser\ParsedClass;

/**
 * @param  array<string, list<string>>  $dependencies
 * @return list<Cycle>
 */
function analyzeCycles(array $dependencies): array
{
    $classes = [];

    foreach ($dependencies as $fqcn => $targets) {
        $classes[] = new ParsedClass($fqcn, ClassKind::Class_, $targets, 'file.php');
    }

    $graph = (new GraphBuilder)->build($classes);

    return (new CycleAnalyzer(new ComponentAnalyzer(new ComponentResolver)))->analyze($graph);
}

it('finds class cycles and the component cycles they imply', function () {
    $cycles = analyzeCycles([
        'App\A\One' => ['App\A\Two'],
        'App\A\Two' => ['App\A\One'],
        'App\B\One' => ['App\C\One'],
        'App\C\One' => ['App\B\One'],
    ]);

    expect($cycles)->toHaveCount(3)
        ->and($cycles[0]->level)->toBe(CycleLevel::Class_)
        ->and($cycles[0]->members)->toBe(['App\A\One', 'App\A\Two'])
        ->and($cycles[0]->size)->toBe(2)
        ->and($cycles[1]->level)->toBe(CycleLevel::Class_)
        ->and($cycles[1]->members)->toBe(['App\B\One', 'App\C\One'])
        ->and($cycles[2]->level)->toBe(CycleLevel::Component)
        ->and($cycles[2]->members)->toBe(['App\B', 'App\C']);
});

it('reports nested class cycles as a single cycle', function () {
    $cycles = analyzeCycles([
        'App\X\A' => ['App\X\B'],
        'App\X\B' => ['App\X\C', 'App\X\D'],
        'App\X\C' => ['App\X\A'],
        'App\X\D' => ['App\X\B'],
    ]);

    expect($cycles)->toHaveCount(1)
        ->and($cycles[0]->members)->toBe(['App\X\A', 'App\X\B', 'App\X\C', 'App\X\D'])
        ->and($cycles[0]->size)->toBe(4);
});

it('finds no cycle in a DAG', function () {
    $cycles = analyzeCycles([
        'App\A\One' => ['App\B\One', 'App\C\One'],
        'App\B\One' => ['App\C\One'],
        'App\C\One' => [],
    ]);

    expect($cycles)->toBe([]);
});

it('finds a component cycle without any class cycle', function () {
    $cycles = analyzeCycles([
        'App\A\One' => ['App\B\One'],
        'App\B\One' => [],
        'App\B\Two' => ['App\A\Two'],
        'App\A\Two' => [],
    ]);

    expect($cycles)->toHaveCount(1)
        ->and($cycles[0]->level)->toBe(CycleLevel::Component)
        ->and($cycles[0]->members)->toBe(['App\A', 'App\B'])
        ->and($cycles[0]->size)->toBe(2);
});
