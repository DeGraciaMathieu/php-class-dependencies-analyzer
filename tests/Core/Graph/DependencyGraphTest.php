<?php

use App\Core\Graph\DependencyGraph;
use App\Core\Graph\Filter;
use App\Core\Graph\GraphBuilder;
use App\Core\Parser\ClassKind;
use App\Core\Parser\ParsedClass;

function parsedClass(string $fqcn, array $dependencies = [], string $file = 'file.php'): ParsedClass
{
    return new ParsedClass($fqcn, ClassKind::Class_, $dependencies, $file);
}

function cyclicGraph(): DependencyGraph
{
    return (new GraphBuilder)->build([
        parsedClass('C', ['A']),
        parsedClass('A', ['B', 'DateTime']),
        parsedClass('B', ['C']),
        parsedClass('D', ['A']),
    ]);
}

it('separates internal and external dependencies', function () {
    $graph = cyclicGraph();

    expect($graph->count())->toBe(4)
        ->and($graph->dependenciesOf('A'))->toBe(['B'])
        ->and($graph->externalDependenciesOf('A'))->toBe(['DateTime'])
        ->and($graph->efferent('A'))->toBe(1)
        ->and($graph->has('DateTime'))->toBeFalse();
});

it('indexes dependents', function () {
    $graph = cyclicGraph();

    expect($graph->dependentsOf('A'))->toBe(['C', 'D'])
        ->and($graph->afferent('A'))->toBe(2)
        ->and($graph->afferent('D'))->toBe(0)
        ->and($graph->afferent('B'))->toBe(1);
});

it('exposes nodes sorted by fqcn', function () {
    $graph = cyclicGraph();

    expect(array_keys($graph->nodes()))->toBe(['A', 'B', 'C', 'D'])
        ->and($graph->node('A')->kind)->toBe(ClassKind::Class_)
        ->and($graph->node('A')->file)->toBe('file.php');
});

it('sorts dependency lists', function () {
    $graph = (new GraphBuilder)->build([
        parsedClass('A', ['Zeta', 'C', 'Alpha', 'B']),
        parsedClass('B'),
        parsedClass('C'),
    ]);

    expect($graph->dependenciesOf('A'))->toBe(['B', 'C'])
        ->and($graph->externalDependenciesOf('A'))->toBe(['Alpha', 'Zeta']);
});

it('returns empty lists for unknown classes', function () {
    $graph = cyclicGraph();

    expect($graph->dependenciesOf('Unknown'))->toBe([])
        ->and($graph->dependentsOf('Unknown'))->toBe([])
        ->and($graph->externalDependenciesOf('Unknown'))->toBe([])
        ->and($graph->filteredOutDependenciesOf('Unknown'))->toBe([]);
});

it('throws on unknown node', function () {
    cyclicGraph()->node('Unknown');
})->throws(InvalidArgumentException::class);

it('ignores self dependencies', function () {
    $graph = (new GraphBuilder)->build([parsedClass('A', ['A'])]);

    expect($graph->dependenciesOf('A'))->toBe([])
        ->and($graph->dependentsOf('A'))->toBe([])
        ->and($graph->externalDependenciesOf('A'))->toBe([]);
});

it('keeps the first declaration of a duplicated class and reports it', function () {
    $graph = (new GraphBuilder)->build([
        parsedClass('A', ['B'], 'first.php'),
        parsedClass('B'),
        parsedClass('A', ['C'], 'second.php'),
    ]);

    expect($graph->count())->toBe(2)
        ->and($graph->node('A')->file)->toBe('first.php')
        ->and($graph->dependenciesOf('A'))->toBe(['B'])
        ->and($graph->duplicates())->toBe(['A']);
});

it('is independent of the input order', function () {
    $classes = [
        parsedClass('A', ['B', 'DateTime']),
        parsedClass('B', ['C']),
        parsedClass('C', ['A']),
        parsedClass('D', ['A']),
    ];

    $graph = (new GraphBuilder)->build($classes);
    $reversedGraph = (new GraphBuilder)->build(array_reverse($classes));

    expect($reversedGraph)->toEqual($graph);
});

it('accepts a generator', function () {
    $classes = (function () {
        yield parsedClass('A', ['B']);
        yield parsedClass('B');
    })();

    expect((new GraphBuilder)->build($classes)->dependentsOf('B'))->toBe(['A']);
});

it('builds a large graph', function () {
    $classes = [];

    for ($i = 0; $i < 5000; $i++) {
        $dependencies = [];

        for ($j = 1; $j <= 10; $j++) {
            $dependencies[] = 'App\\Class'.(($i + $j) % 5000);
        }

        $classes[] = parsedClass('App\\Class'.$i, $dependencies);
    }

    $graph = (new GraphBuilder)->build($classes);

    expect($graph->count())->toBe(5000)
        ->and($graph->efferent('App\\Class0'))->toBe(10)
        ->and($graph->afferent('App\\Class0'))->toBe(10)
        ->and($graph->dependentsOf('App\\Class0'))->toContain('App\\Class4999')
        ->and($graph->externalDependenciesOf('App\\Class0'))->toBe([]);
});

it('builds a subgraph keeping edges between kept nodes', function () {
    $subgraph = cyclicGraph()->subgraph(new Filter(exclude: ['B']));

    expect(array_keys($subgraph->nodes()))->toBe(['A', 'C', 'D'])
        ->and($subgraph->dependenciesOf('A'))->toBe([])
        ->and($subgraph->filteredOutDependenciesOf('A'))->toBe(['B'])
        ->and($subgraph->externalDependenciesOf('A'))->toBe(['DateTime'])
        ->and($subgraph->dependentsOf('C'))->toBe([])
        ->and($subgraph->dependentsOf('A'))->toBe(['C', 'D']);
});

it('accumulates filtered out dependencies across subgraphs', function () {
    $subgraph = cyclicGraph()
        ->subgraph(new Filter(exclude: ['B']))
        ->subgraph(new Filter(exclude: ['C']));

    expect($subgraph->filteredOutDependenciesOf('A'))->toBe(['B'])
        ->and($subgraph->dependentsOf('A'))->toBe(['D']);
});

it('does not modify the original graph when subgraphing', function () {
    $graph = cyclicGraph();

    $graph->subgraph(new Filter(only: ['A']));

    expect($graph->count())->toBe(4)
        ->and($graph->dependenciesOf('A'))->toBe(['B'])
        ->and($graph->filteredOutDependenciesOf('A'))->toBe([]);
});

it('keeps only matching duplicates in a subgraph', function () {
    $graph = (new GraphBuilder)->build([
        parsedClass('A'),
        parsedClass('A'),
        parsedClass('B'),
        parsedClass('B'),
    ]);

    expect($graph->subgraph(new Filter(only: ['B']))->duplicates())->toBe(['B']);
});
