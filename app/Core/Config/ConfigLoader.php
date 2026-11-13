<?php

declare(strict_types=1);

namespace App\Core\Config;

use Throwable;

final readonly class ConfigLoader
{
    public const FILENAME = 'dependencies.php';

    public function find(string $directory): ?string
    {
        $file = rtrim($directory, '/').'/'.self::FILENAME;

        return is_file($file) ? $file : null;
    }

    public function load(string $file): Config
    {
        if (! is_file($file)) {
            throw new ConfigException("Configuration file [{$file}] not found.");
        }

        $config = $this->requireIsolated($file);

        if (! $config instanceof Config) {
            $type = get_debug_type($config);

            throw new ConfigException("Configuration file [{$file}] must return a ".Config::class.", [{$type}] returned.");
        }

        return $config;
    }

    /**
     * The static closure keeps $this and the loader's local variables out of the file's scope.
     */
    private function requireIsolated(string $file): mixed
    {
        try {
            return (static fn (string $file): mixed => require $file)($file);
        } catch (Throwable $e) {
            throw new ConfigException("Configuration file [{$file}] failed: {$e->getMessage()}", previous: $e);
        }
    }
}
