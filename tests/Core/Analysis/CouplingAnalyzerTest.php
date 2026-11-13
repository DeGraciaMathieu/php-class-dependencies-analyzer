<?php

use App\Core\Analysis\CouplingAnalyzer;
use App\Core\Graph\GraphBuilder;
use App\Core\Parser\ClassKind;
use App\Core\Parser\ParsedClass;

function couplingMetrics(array $dependencies, ClassKind $kind = ClassKind::Class_): array
{
    $classes = [];

    foreach ($dependencies as $fqcn => $classDependencies) {
        $classes[] = new ParsedClass($fqcn, $kind, $classDependencies, 'file.php');
    }

    return (new CouplingAnalyzer)->analyze((new GraphBuilder)->build($classes));
}

it('computes afferent, efferent and external coupling', function () {
    $metrics = couplingMetrics([
        'A' => ['B', 'DateTime'],
        'B' => ['C'],
        'C' => ['A'],
        'D' => ['A'],
    ]);

    expect($metrics['A']->afferent)->toBe(2)
        ->and($metrics['A']->efferent)->toBe(1)
        ->and($metrics['A']->externalCount)->toBe(1)
        ->and($metrics['B']->afferent)->toBe(1)
        ->and($metrics['B']->efferent)->toBe(1)
        ->and($metrics['B']->externalCount)->toBe(0)
        ->and($metrics['D']->afferent)->toBe(0)
        ->and($metrics['D']->efferent)->toBe(1);
});

it('computes instability with internal efferent coupling only', function () {
    $metrics = couplingMetrics([
        'A' => ['B', 'DateTime'],
        'B' => ['C'],
        'C' => ['A'],
        'D' => ['A'],
    ]);

    expect($metrics['A']->instability)->toEqualWithDelta(1 / 3, 1e-12)
        ->and($metrics['B']->instability)->toBe(0.5)
        ->and($metrics['D']->instability)->toBe(1.0);
});

it('does not round instability', function () {
    $metrics = couplingMetrics([
        'A' => ['B'],
        'B' => [],
        'C' => ['B'],
        'D' => ['B'],
        'E' => ['A'],
        'F' => ['A'],
    ]);

    expect($metrics['A']->instability)->toBe(1 / 3)
        ->and($metrics['A']->instability)->not->toBe(round(1 / 3, 2));
});

it('gives a zero instability to an isolated class', function () {
    $metrics = couplingMetrics(['A' => ['DateTime']]);

    expect($metrics['A']->afferent)->toBe(0)
        ->and($metrics['A']->efferent)->toBe(0)
        ->and($metrics['A']->externalCount)->toBe(1)
        ->and($metrics['A']->instability)->toBe(0.0);
});

it('indexes metrics by fqcn in sorted order and keeps the kind', function () {
    $metrics = couplingMetrics(['C' => [], 'A' => [], 'B' => []], ClassKind::Interface_);

    expect(array_keys($metrics))->toBe(['A', 'B', 'C'])
        ->and($metrics['A']->fqcn)->toBe('A')
        ->and($metrics['A']->kind)->toBe(ClassKind::Interface_);
});
