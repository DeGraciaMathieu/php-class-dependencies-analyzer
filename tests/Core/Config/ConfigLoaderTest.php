<?php

use App\Core\Config\Config;
use App\Core\Config\ConfigException;
use App\Core\Config\ConfigLoader;

function configFixture(string $name): string
{
    return dirname(__DIR__, 2).'/Fixtures/Core/Config/'.$name;
}

it('loads a configuration file', function () {
    $config = (new ConfigLoader)->load(configFixture('valid/dependencies.php'));

    expect($config)->toBeInstanceOf(Config::class)
        ->and($config->paths)->toBe(['src'])
        ->and($config->ignore)->toBe(['App\Tests\*'])
        ->and($config->componentDepth)->toBe(3);
});

it('fails when the file does not exist', function () {
    (new ConfigLoader)->load(configFixture('missing.php'));
})->throws(ConfigException::class, 'not found');

it('fails when the file does not return a config', function () {
    (new ConfigLoader)->load(configFixture('returns-array.php'));
})->throws(ConfigException::class, '[array] returned');

it('wraps an exception thrown by the file', function () {
    try {
        (new ConfigLoader)->load(configFixture('throws.php'));
    } catch (ConfigException $e) {
        expect($e->getMessage())->toContain('Broken configuration')
            ->and($e->getPrevious())->toBeInstanceOf(RuntimeException::class)
            ->and($e->getPrevious()->getMessage())->toBe('Broken configuration');

        return;
    }

    $this->fail('ConfigException not thrown.');
});

it('finds the dependencies.php file of a directory', function () {
    expect((new ConfigLoader)->find(configFixture('valid')))->toBe(configFixture('valid/dependencies.php'))
        ->and((new ConfigLoader)->find(configFixture('valid/')))->toBe(configFixture('valid/dependencies.php'));
});

it('finds nothing in a directory without dependencies.php', function () {
    expect((new ConfigLoader)->find(configFixture('')))->toBeNull();
});
