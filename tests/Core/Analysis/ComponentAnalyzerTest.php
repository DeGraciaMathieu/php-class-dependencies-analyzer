<?php

use App\Core\Analysis\ComponentAnalyzer;
use App\Core\Analysis\ComponentResolver;
use App\Core\Graph\DependencyGraph;
use App\Core\Graph\GraphBuilder;
use App\Core\Parser\ClassKind;
use App\Core\Parser\ParsedClass;

function componentGraph(): DependencyGraph
{
    return (new GraphBuilder)->build([
        new ParsedClass('App\Billing\Invoice', ClassKind::Class_, ['App\Billing\Gateway', 'App\Shared\Money', 'DateTime'], 'file.php'),
        new ParsedClass('App\Billing\Gateway', ClassKind::Interface_, ['App\Shared\Money'], 'file.php'),
        new ParsedClass('App\Billing\HasTotal', ClassKind::Trait_, ['App\Shared\Currency'], 'file.php'),
        new ParsedClass('App\Shared\Money', ClassKind::Class_, ['App\Shared\Currency'], 'file.php'),
        new ParsedClass('App\Shared\Currency', ClassKind::Enum_, [], 'file.php'),
        new ParsedClass('App\Http\InvoiceController', ClassKind::Class_, ['App\Billing\Invoice', 'App\Billing\Gateway'], 'file.php'),
        new ParsedClass('App\Http\Middleware', ClassKind::AbstractClass, ['App\Billing\Invoice'], 'file.php'),
    ]);
}

function analyzeComponents(): array
{
    return (new ComponentAnalyzer(new ComponentResolver))->analyze(componentGraph());
}

it('groups classes into components sorted by name', function () {
    $metrics = analyzeComponents();

    expect(array_keys($metrics))->toBe(['App\Billing', 'App\Http', 'App\Shared'])
        ->and($metrics['App\Billing']->name)->toBe('App\Billing')
        ->and($metrics['App\Billing']->classes)->toBe(['App\Billing\Gateway', 'App\Billing\HasTotal', 'App\Billing\Invoice']);
});

it('computes abstractness excluding traits', function () {
    $metrics = analyzeComponents();

    expect($metrics['App\Billing']->classCount)->toBe(2)
        ->and($metrics['App\Billing']->abstractCount)->toBe(1)
        ->and($metrics['App\Billing']->abstractness)->toBe(0.5)
        ->and($metrics['App\Shared']->abstractness)->toBe(0.0);
});

it('counts distinct outside classes for afferent and efferent coupling', function () {
    $metrics = analyzeComponents();

    expect($metrics['App\Billing']->afferent)->toBe(2)
        ->and($metrics['App\Billing']->efferent)->toBe(2)
        ->and($metrics['App\Http']->afferent)->toBe(0)
        ->and($metrics['App\Http']->efferent)->toBe(2)
        ->and($metrics['App\Shared']->afferent)->toBe(3)
        ->and($metrics['App\Shared']->efferent)->toBe(0);
});

it('computes instability and distance from the main sequence', function () {
    $metrics = analyzeComponents();

    expect($metrics['App\Billing']->instability)->toBe(0.5)
        ->and($metrics['App\Billing']->distance)->toBe(0.0)
        ->and($metrics['App\Http']->abstractness)->toBe(0.5)
        ->and($metrics['App\Http']->instability)->toBe(1.0)
        ->and($metrics['App\Http']->distance)->toBe(0.5)
        ->and($metrics['App\Shared']->instability)->toBe(0.0)
        ->and($metrics['App\Shared']->distance)->toBe(1.0);
});

it('does not round metrics', function () {
    $graph = (new GraphBuilder)->build([
        new ParsedClass('App\A\One', ClassKind::Interface_, [], 'file.php'),
        new ParsedClass('App\A\Two', ClassKind::Class_, ['App\B\Foo'], 'file.php'),
        new ParsedClass('App\A\Three', ClassKind::Class_, [], 'file.php'),
        new ParsedClass('App\B\Foo', ClassKind::Class_, [], 'file.php'),
    ]);

    $metrics = (new ComponentAnalyzer(new ComponentResolver))->analyze($graph);

    expect($metrics['App\A']->abstractness)->toEqualWithDelta(1 / 3, 1e-12)
        ->and($metrics['App\A']->distance)->toEqualWithDelta(1 / 3, 1e-12);
});

it('gives zero metrics to a component made of traits only', function () {
    $graph = (new GraphBuilder)->build([
        new ParsedClass('App\Concerns\HasName', ClassKind::Trait_, [], 'file.php'),
    ]);

    $metrics = (new ComponentAnalyzer(new ComponentResolver))->analyze($graph);

    expect($metrics['App\Concerns']->classCount)->toBe(0)
        ->and($metrics['App\Concerns']->abstractness)->toBe(0.0)
        ->and($metrics['App\Concerns']->instability)->toBe(0.0)
        ->and($metrics['App\Concerns']->distance)->toBe(1.0);
});

it('uses named component definitions', function () {
    $analyzer = new ComponentAnalyzer(new ComponentResolver(['Core' => ['App\Billing\*', 'App\Shared\*']]));

    $metrics = $analyzer->analyze(componentGraph());

    expect(array_keys($metrics))->toBe(['App\Http', 'Core'])
        ->and($metrics['Core']->efferent)->toBe(0)
        ->and($metrics['Core']->afferent)->toBe(2);
});

it('lists the components each component depends on', function () {
    $dependencies = (new ComponentAnalyzer(new ComponentResolver))->dependencies(componentGraph());

    expect($dependencies)->toBe([
        'App\Billing' => ['App\Shared'],
        'App\Http' => ['App\Billing'],
        'App\Shared' => [],
    ]);
});
