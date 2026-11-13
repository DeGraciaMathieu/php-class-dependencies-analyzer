<?php

use App\Core\Config\Config;
use App\Core\Config\ConfigException;
use App\Core\Graph\Filter;
use App\Core\Rules\Rules;

it('has defaults', function () {
    $config = Config::make();

    expect($config->paths)->toBe([])
        ->and($config->exclude)->toBe([])
        ->and($config->only)->toBe([])
        ->and($config->ignore)->toBe([])
        ->and($config->components)->toBe([])
        ->and($config->componentDepth)->toBe(2)
        ->and($config->rules)->toBe([])
        ->and($config->baseline)->toBeNull()
        ->and($config->cache)->toBeTrue();
});

it('returns a new instance without changing the original', function () {
    $original = Config::make()->paths(['src']);

    $changed = $original->paths(['app'])->componentDepth(3);

    expect($changed)->not->toBe($original)
        ->and($original->paths)->toBe(['src'])
        ->and($original->componentDepth)->toBe(2)
        ->and($changed->paths)->toBe(['app'])
        ->and($changed->componentDepth)->toBe(3);
});

it('declares rules', function () {
    $rules = [Rules::noCycles(), Rules::stableDependencies(0.1)];

    expect(Config::make()->rules($rules)->rules)->toBe($rules);
});

it('declares a baseline file', function () {
    expect(Config::make()->baseline('dependencies-baseline.json')->baseline)->toBe('dependencies-baseline.json');
});

it('enables, disables or relocates the parse cache', function (bool|string $cache) {
    expect(Config::make()->cache($cache)->cache)->toBe($cache);
})->with([true, false, 'build/.dependencies-cache']);

it('builds analysis options', function () {
    $options = Config::make()
        ->paths(['src'])
        ->exclude(['src/Legacy/*'])
        ->only(['App\*'])
        ->ignore(['App\Tests\*'])
        ->components(['Billing' => 'App\Billing\*'])
        ->componentDepth(3)
        ->toOptions('/project');

    expect($options->paths)->toBe(['src'])
        ->and($options->basePath)->toBe('/project')
        ->and($options->excludePaths)->toBe(['src/Legacy/*'])
        ->and($options->filter)->toEqual(new Filter(only: ['App\*'], exclude: ['App\Tests\*']))
        ->and($options->components)->toBe(['Billing' => 'App\Billing\*'])
        ->and($options->componentDepth)->toBe(3);
});

it('requires at least one path', function () {
    Config::make()->toOptions('/project');
})->throws(ConfigException::class, 'No path to analyze');

it('rejects a component without glob', function () {
    Config::make()->paths(['src'])->components(['Billing' => []])->toOptions('/project');
})->throws(ConfigException::class, 'Component [Billing] has no glob.');

it('rejects a component depth below 1', function () {
    Config::make()->paths(['src'])->componentDepth(0)->toOptions('/project');
})->throws(ConfigException::class, 'Component depth must be at least 1');
