<?php

use App\Core\Cache\ParseCache;
use App\Core\Parser\ClassKind;
use App\Core\Parser\ParsedClass;
use App\Core\Parser\ParseError;
use App\Core\Parser\ParseResult;

beforeEach(function () {
    $this->directory = sys_get_temp_dir().'/parse-cache-'.uniqid();
    $this->file = $this->directory.'/'.ParseCache::FILENAME;
    $this->result = new ParseResult(
        [new ParsedClass('App\Foo', ClassKind::Class_, ['App\Bar'], '/project/Foo.php')],
        [new ParseError('/project/Foo.php', 'Syntax error')],
    );
    mkdir($this->directory);
});

afterEach(function () {
    array_map(unlink(...), glob($this->directory.'/{,.}[!.]*', GLOB_BRACE) ?: []);
    rmdir($this->directory);
});

function savedCache(string $file, string $signature, array $entries): void
{
    $cache = new ParseCache($file, $signature);
    $cache->load();

    foreach ($entries as $path => [$hash, $result]) {
        $cache->put($path, $hash, $result);
    }

    $cache->save();
}

function loadedCache(string $file, string $signature = 'v1'): ParseCache
{
    $cache = new ParseCache($file, $signature);
    $cache->load();

    return $cache;
}

it('is empty without file', function () {
    expect(loadedCache($this->file)->get('/project/Foo.php', 'hash'))->toBeNull();
});

it('returns a saved result while the hash is unchanged', function () {
    savedCache($this->file, 'v1', ['/project/Foo.php' => ['hash', $this->result]]);

    $cache = loadedCache($this->file);

    expect($cache->get('/project/Foo.php', 'hash'))->toEqual($this->result)
        ->and($cache->get('/project/Foo.php', 'other-hash'))->toBeNull();
});

it('ignores a cache saved with another signature', function () {
    savedCache($this->file, 'v1', ['/project/Foo.php' => ['hash', $this->result]]);

    expect(loadedCache($this->file, 'v2')->get('/project/Foo.php', 'hash'))->toBeNull();
});

it('ignores a corrupted cache without exception', function (string $content) {
    file_put_contents($this->file, $content);

    expect(loadedCache($this->file)->get('/project/Foo.php', 'hash'))->toBeNull();
})->with([
    'garbage' => ['not a serialized value'],
    'truncated' => [substr(serialize(['format' => ParseCache::FORMAT, 'signature' => 'v1', 'entries' => []]), 0, 20)],
    'unexpected shape' => [serialize(['format' => ParseCache::FORMAT, 'signature' => 'v1', 'entries' => 'foo'])],
    'unexpected class' => [serialize(['format' => ParseCache::FORMAT, 'signature' => 'v1', 'entries' => [
        '/project/Foo.php' => ['hash' => 'hash', 'result' => new ArrayObject],
    ]])],
]);

it('rewrites a corrupted cache on save', function () {
    file_put_contents($this->file, 'not a serialized value');

    $cache = loadedCache($this->file);
    $cache->put('/project/Foo.php', 'hash', $this->result);
    $cache->save();

    expect(loadedCache($this->file)->get('/project/Foo.php', 'hash'))->toEqual($this->result);
});

it('only saves the entries used during the run', function () {
    savedCache($this->file, 'v1', [
        '/project/Foo.php' => ['hash', $this->result],
        '/project/Removed.php' => ['hash', $this->result],
    ]);

    $cache = loadedCache($this->file);
    $cache->get('/project/Foo.php', 'hash');
    $cache->save();

    $reloaded = loadedCache($this->file);

    expect($reloaded->get('/project/Foo.php', 'hash'))->toEqual($this->result)
        ->and($reloaded->get('/project/Removed.php', 'hash'))->toBeNull();
});

it('leaves no temporary file after saving', function () {
    savedCache($this->file, 'v1', ['/project/Foo.php' => ['hash', $this->result]]);

    expect(glob($this->directory.'/{,.}*.tmp', GLOB_BRACE))->toBe([])
        ->and($this->file)->toBeFile();
});

it('clears the cache file and the loaded entries', function () {
    savedCache($this->file, 'v1', ['/project/Foo.php' => ['hash', $this->result]]);

    $cache = loadedCache($this->file);
    $cache->clear();

    expect($this->file)->not->toBeFile()
        ->and($cache->get('/project/Foo.php', 'hash'))->toBeNull();
});
