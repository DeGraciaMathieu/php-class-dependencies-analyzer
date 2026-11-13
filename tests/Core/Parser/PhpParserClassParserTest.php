<?php

use App\Core\Parser\ClassKind;
use App\Core\Parser\ParsedClass;
use App\Core\Parser\PhpParser\PhpParserClassParser;

function parserFixturePath(string $file): string
{
    return dirname(__DIR__, 2).'/Fixtures/'.$file;
}

function parseFixture(string $file): array
{
    return (new PhpParserClassParser)->parse(parserFixturePath($file))->classes;
}

function dependenciesOf(string $file): array
{
    return parseFixture($file)[0]->dependencies;
}

it('builds a parsed class with its fqcn, kind and file', function () {
    $file = parserFixturePath('Core/Parser/CompleteClass.php');

    $classes = (new PhpParserClassParser)->parse($file)->classes;

    expect($classes)->toHaveCount(1)
        ->and($classes[0])->toBeInstanceOf(ParsedClass::class)
        ->and($classes[0]->fqcn)->toBe('Tests\Fixtures\Core\Parser\CompleteClass')
        ->and($classes[0]->kind)->toBe(ClassKind::Class_)
        ->and($classes[0]->file)->toBe($file);
});

it('collects dependencies in every class position', function (string $dependency) {
    expect(dependenciesOf('Core/Parser/CompleteClass.php'))->toContain($dependency);
})->with([
    'extends' => 'Fixture\Support\Base',
    'implements' => 'Fixture\Contracts\Cacheable',
    'implements through an alias' => 'Fixture\Contracts\Countable',
    'attribute' => 'Fixture\Attributes\Route',
    'trait use' => 'Fixture\Support\HasEvents',
    'nullable property type' => 'Fixture\Domain\Address',
    'union parameter type' => 'Fixture\Domain\Money',
    'union parameter type (second member)' => 'Fixture\Domain\Order',
    'intersection parameter type' => 'Fixture\Domain\Left',
    'intersection parameter type (second member)' => 'Fixture\Domain\Right',
    'new' => 'Fixture\Domain\Item',
    'static call' => 'Fixture\Support\Calculator',
    'class constant' => 'Fixture\Support\Limits',
    'static property' => 'Fixture\Support\Registry',
    '::class' => 'Fixture\Domain\Reference',
    'instanceof' => 'Fixture\Domain\Checked',
    'catch' => 'Fixture\Exceptions\NotFound',
    'catch union' => 'Fixture\Exceptions\Timeout',
    'nullable return type' => 'Fixture\Domain\Result',
]);

it('ignores keywords, native types, functions, constants and the class itself', function () {
    expect(dependenciesOf('Core/Parser/CompleteClass.php'))
        ->not->toContain('self', 'static', 'parent', 'int', 'mixed', 'null')
        ->not->toContain('strlen', 'PHP_EOL', 'Fixture\Support\helper')
        ->not->toContain('Tests\Fixtures\Core\Parser\CompleteClass');
});

it('returns deduplicated dependencies without leading backslash', function () {
    $dependencies = dependenciesOf('Core/Parser/CompleteClass.php');

    expect($dependencies)->toBe(array_values(array_unique($dependencies)))
        ->each->not->toStartWith('\\');
});

it('detects the kind of each classlike', function (string $file, ClassKind $kind, array $dependencies) {
    $classes = parseFixture($file);

    expect($classes)->toHaveCount(1)
        ->and($classes[0]->kind)->toBe($kind)
        ->and($classes[0]->dependencies)->toBe($dependencies);
})->with([
    'abstract class' => ['Core/Parser/AbstractService.php', ClassKind::AbstractClass, ['Fixture\Domain\Result']],
    'interface' => ['Core/Parser/Repository.php', ClassKind::Interface_, ['Fixture\Contracts\Readable', 'Fixture\Contracts\Writable', 'Fixture\Domain\Order']],
    'trait' => ['Core/Parser/Loggable.php', ClassKind::Trait_, ['Fixture\Support\Logger']],
    'enum' => ['Core/Parser/Status.php', ClassKind::Enum_, ['Fixture\Contracts\HasLabel', 'Fixture\Support\Label']],
]);

it('returns one parsed class per named classlike', function () {
    $classes = parseFixture('Core/Parser/MultipleClasses.php');

    expect($classes)->toHaveCount(2)
        ->and($classes[0]->fqcn)->toBe('Tests\Fixtures\Core\Parser\First')
        ->and($classes[0]->dependencies)->toBe(['Fixture\Domain\AnonymousParent'])
        ->and($classes[1]->fqcn)->toBe('Tests\Fixtures\Core\Parser\Second')
        ->and($classes[1]->dependencies)->toBe(['Tests\Fixtures\Core\Parser\First']);
});

it('returns an empty result for a file without classlike', function () {
    $result = (new PhpParserClassParser)->parse(parserFixturePath('Core/Parser/NoClass.php'));

    expect($result->classes)->toBe([])
        ->and($result->errors)->toBe([]);
});

it('reports a syntax error without throwing', function () {
    $file = parserFixturePath('Core/Parser/Invalid.php');

    $result = (new PhpParserClassParser)->parse($file);

    expect($result->classes)->toBe([])
        ->and($result->errors)->toHaveCount(1)
        ->and($result->errors[0]->file)->toBe($file)
        ->and($result->errors[0]->message)->toContain('Syntax error');
});

it('keeps native php classes as dependencies', function () {
    $classes = parseFixture('Php85/ModernClass.php');
    $modernClass = $classes[4];

    expect($classes)->toHaveCount(5)
        ->and($modernClass->fqcn)->toBe('Tests\Fixtures\Php85\ModernClass')
        ->and($modernClass->dependencies)->toBe([
            'Tests\Fixtures\Php85\AbstractBase',
            'Tests\Fixtures\Php85\Contract',
            'IteratorAggregate',
            'DateTimeInterface',
            'Tests\Fixtures\Php85\CustomAttribute',
            'Tests\Fixtures\Php85\Status',
            'Traversable',
            'ArrayIterator',
        ]);
});

it('flags abstract kinds', function () {
    expect(ClassKind::AbstractClass->isAbstract())->toBeTrue()
        ->and(ClassKind::Interface_->isAbstract())->toBeTrue()
        ->and(ClassKind::Class_->isAbstract())->toBeFalse()
        ->and(ClassKind::Trait_->isAbstract())->toBeFalse()
        ->and(ClassKind::Enum_->isAbstract())->toBeFalse();
});
