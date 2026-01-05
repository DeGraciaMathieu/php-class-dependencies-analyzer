<?php

use App\Infrastructure\Analyze\Adapters\PhpParser\PhpAstClassDependenciesParser;

it('detects dependencies from modern PHP syntax (8.1+) ', function () {
    $parser = app(PhpAstClassDependenciesParser::class);

    $analysis = $parser->parse(__DIR__ . '/../../../Fixtures/Php85/ModernClass.php');

    expect($analysis->fqcn())->toBe('Tests\\Fixtures\\Php85\\ModernClass');

    expect($analysis->dependencies())->toContain(
        'Tests\\Fixtures\\Php85\\AbstractBase',
        'Tests\\Fixtures\\Php85\\Contract',
        'IteratorAggregate',
        'DateTimeInterface',
        'Tests\\Fixtures\\Php85\\Status',
        'Tests\\Fixtures\\Php85\\CustomAttribute',
        'ArrayIterator',
        'Traversable',
    );
});

it('marks interface and abstract correctly', function () {
    $parser = app(PhpAstClassDependenciesParser::class);

    $analysis = $parser->parse(__DIR__ . '/../../../Fixtures/Php85/ModernClass.php');

    expect($analysis->isAbstract())->toBeFalse();
    expect($analysis->isInterface())->toBeFalse();
});
