<?php

use App\Core\Cache\CachedClassParser;
use App\Core\Cache\ParseCache;
use App\Core\Parser\ClassParser;
use App\Core\Parser\ParseResult;

beforeEach(function () {
    $this->directory = sys_get_temp_dir().'/cached-parser-'.uniqid();
    $this->cacheFile = $this->directory.'/'.ParseCache::FILENAME;
    mkdir($this->directory);

    file_put_contents($this->directory.'/Foo.php', '<?php class Foo {}');
    file_put_contents($this->directory.'/Bar.php', '<?php class Bar {}');

    $this->spy = new class implements ClassParser
    {
        public int $calls = 0;

        public function parse(string $file): ParseResult
        {
            $this->calls++;

            return new ParseResult;
        }
    };
});

afterEach(function () {
    array_map(unlink(...), glob($this->directory.'/{,.}[!.]*', GLOB_BRACE) ?: []);
    rmdir($this->directory);
});

/**
 * Parses the files like a run of the analysis: load, parse, save.
 */
function cachedRun(ClassParser $inner, string $cacheFile, array $files, string $signature = 'v1'): CachedClassParser
{
    $cache = new ParseCache($cacheFile, $signature);
    $cache->load();

    $parser = new CachedClassParser($inner, $cache);

    foreach ($files as $file) {
        $parser->parse($file);
    }

    $cache->save();

    return $parser;
}

it('parses every file on the first run', function () {
    $parser = cachedRun($this->spy, $this->cacheFile, [$this->directory.'/Foo.php', $this->directory.'/Bar.php']);

    expect($this->spy->calls)->toBe(2)
        ->and($parser->hits())->toBe(0)
        ->and($parser->misses())->toBe(2);
});

it('parses nothing on a second run without change', function () {
    $files = [$this->directory.'/Foo.php', $this->directory.'/Bar.php'];
    cachedRun($this->spy, $this->cacheFile, $files);
    $this->spy->calls = 0;

    $parser = cachedRun($this->spy, $this->cacheFile, $files);

    expect($this->spy->calls)->toBe(0)
        ->and($parser->hits())->toBe(2)
        ->and($parser->misses())->toBe(0);
});

it('only parses the modified file', function () {
    $files = [$this->directory.'/Foo.php', $this->directory.'/Bar.php'];
    cachedRun($this->spy, $this->cacheFile, $files);
    $this->spy->calls = 0;

    file_put_contents($this->directory.'/Foo.php', '<?php class Foo extends Bar {}');
    $parser = cachedRun($this->spy, $this->cacheFile, $files);

    expect($this->spy->calls)->toBe(1)
        ->and($parser->hits())->toBe(1)
        ->and($parser->misses())->toBe(1);
});

it('parses every file again when the signature changes', function () {
    $files = [$this->directory.'/Foo.php', $this->directory.'/Bar.php'];
    cachedRun($this->spy, $this->cacheFile, $files);
    $this->spy->calls = 0;

    cachedRun($this->spy, $this->cacheFile, $files, 'v2');

    expect($this->spy->calls)->toBe(2);
});

it('returns the cached result', function () {
    $file = $this->directory.'/Foo.php';
    cachedRun($this->spy, $this->cacheFile, [$file]);

    $cache = new ParseCache($this->cacheFile, 'v1');
    $cache->load();

    expect((new CachedClassParser($this->spy, $cache))->parse($file))->toEqual(new ParseResult)
        ->and($this->spy->calls)->toBe(1);
});

it('delegates an unreadable file without caching it', function () {
    $missing = $this->directory.'/Missing.php';

    cachedRun($this->spy, $this->cacheFile, [$missing]);
    $parser = cachedRun($this->spy, $this->cacheFile, [$missing]);

    expect($this->spy->calls)->toBe(2)
        ->and($parser->misses())->toBe(1);
});
