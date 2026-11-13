<?php

declare(strict_types=1);

namespace App\Core\Cache;

use App\Core\Parser\ClassParser;
use App\Core\Parser\ParseResult;

/**
 * Only delegates to the inner parser the files whose content changed since they were cached.
 */
final class CachedClassParser implements ClassParser
{
    private int $hits = 0;

    private int $misses = 0;

    public function __construct(
        private readonly ClassParser $inner,
        private readonly ParseCache $cache,
    ) {}

    public function parse(string $file): ParseResult
    {
        $content = is_file($file) && is_readable($file) ? file_get_contents($file) : false;

        if ($content === false) {
            $this->misses++;

            return $this->inner->parse($file);
        }

        $path = realpath($file) ?: $file;
        $hash = sha1($content);
        $cached = $this->cache->get($path, $hash);

        if ($cached !== null) {
            $this->hits++;

            return $cached;
        }

        $this->misses++;

        $result = $this->inner->parse($file);
        $this->cache->put($path, $hash, $result);

        return $result;
    }

    public function hits(): int
    {
        return $this->hits;
    }

    public function misses(): int
    {
        return $this->misses;
    }
}
