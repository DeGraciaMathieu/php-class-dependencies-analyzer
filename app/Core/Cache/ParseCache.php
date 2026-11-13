<?php

declare(strict_types=1);

namespace App\Core\Cache;

use App\Core\Parser\ClassKind;
use App\Core\Parser\ParsedClass;
use App\Core\Parser\ParseError;
use App\Core\Parser\ParseResult;
use Throwable;

/**
 * Parse results indexed by absolute file path, valid while the file content hash is unchanged.
 *
 * The file holds the PHP serialization of:
 *
 * [
 *   'format' => int,
 *   'signature' => string,
 *   'entries' => array<string, array{hash: string, result: ParseResult}>,
 * ]
 *
 * A missing, unreadable or corrupted file, another format or another signature means an empty cache:
 * the cache is an optimization and never fails an analysis.
 */
final class ParseCache
{
    public const FILENAME = '.dependencies-cache';

    public const FORMAT = 1;

    private const ALLOWED_CLASSES = [ParseResult::class, ParsedClass::class, ParseError::class, ClassKind::class];

    /**
     * @var array<string, array{hash: string, result: ParseResult}>
     */
    private array $loaded = [];

    /**
     * Entries read or written during the run, the only ones saved.
     *
     * @var array<string, array{hash: string, result: ParseResult}>
     */
    private array $used = [];

    /**
     * @param  string  $signature  Identifies the tool and parser versions, any change invalidates the whole cache
     */
    public function __construct(
        private readonly string $file,
        private readonly string $signature,
    ) {}

    public function load(): void
    {
        $this->loaded = $this->read();
        $this->used = [];
    }

    public function get(string $path, string $hash): ?ParseResult
    {
        $entry = $this->loaded[$path] ?? null;

        if (! is_array($entry) || ($entry['hash'] ?? null) !== $hash || ! ($entry['result'] ?? null) instanceof ParseResult) {
            return null;
        }

        $this->used[$path] = $entry;

        return $entry['result'];
    }

    public function put(string $path, string $hash, ParseResult $result): void
    {
        $this->used[$path] = ['hash' => $hash, 'result' => $result];
    }

    /**
     * Writes a temporary file then renames it, so that a concurrent run never reads a partial cache.
     */
    public function save(): void
    {
        $temporary = $this->file.'.'.uniqid('', true).'.tmp';
        $content = serialize(['format' => self::FORMAT, 'signature' => $this->signature, 'entries' => $this->used]);

        if (@file_put_contents($temporary, $content) === false || ! @rename($temporary, $this->file)) {
            @unlink($temporary);
        }
    }

    public function clear(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }

        $this->loaded = [];
        $this->used = [];
    }

    /**
     * @return array<string, array{hash: string, result: ParseResult}>
     */
    private function read(): array
    {
        $content = is_file($this->file) && is_readable($this->file) ? file_get_contents($this->file) : false;

        if ($content === false) {
            return [];
        }

        // A corrupted cache is ignored without warning nor exception, it is rewritten on save.
        set_error_handler(static fn (): bool => true);

        try {
            $data = unserialize($content, ['allowed_classes' => self::ALLOWED_CLASSES]);
        } catch (Throwable) {
            return [];
        } finally {
            restore_error_handler();
        }

        if (! is_array($data)
            || ($data['format'] ?? null) !== self::FORMAT
            || ($data['signature'] ?? null) !== $this->signature
            || ! is_array($data['entries'] ?? null)) {
            return [];
        }

        return $data['entries'];
    }
}
