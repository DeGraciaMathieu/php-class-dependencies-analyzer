<?php

declare(strict_types=1);

namespace App\Core\Parser;

use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class SourceScanner
{
    /**
     * @param  list<string>  $paths  Files or directories, absolute or relative to $basePath
     * @param  list<string>  $excludeGlobs  Globs matched against paths relative to $basePath
     * @return list<string> Absolute paths of PHP files, sorted
     */
    public function scan(array $paths, array $excludeGlobs, string $basePath): array
    {
        $basePath = $this->realPathOf($basePath);
        $files = [];

        foreach ($paths as $path) {
            foreach ($this->phpFilesIn($this->resolve($path, $basePath)) as $file) {
                if (! $this->isExcluded($file, $excludeGlobs, $basePath)) {
                    $files[$file] = $file;
                }
            }
        }

        sort($files);

        return $files;
    }

    private function resolve(string $path, string $basePath): string
    {
        return $this->realPathOf(str_starts_with($path, '/') ? $path : $basePath.'/'.$path);
    }

    private function realPathOf(string $path): string
    {
        $realPath = realpath($path);

        if ($realPath === false) {
            throw new InvalidArgumentException("Path [{$path}] does not exist.");
        }

        return $realPath;
    }

    /**
     * @return iterable<string>
     */
    private function phpFilesIn(string $path): iterable
    {
        if (is_file($path)) {
            if (str_ends_with($path, '.php')) {
                yield $path;
            }

            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                yield $file->getPathname();
            }
        }
    }

    /**
     * @param  list<string>  $excludeGlobs
     */
    private function isExcluded(string $file, array $excludeGlobs, string $basePath): bool
    {
        $relativePath = str_starts_with($file, $basePath.'/')
            ? substr($file, strlen($basePath) + 1)
            : $file;

        foreach ($excludeGlobs as $glob) {
            if (fnmatch($glob, $relativePath)) {
                return true;
            }
        }

        return false;
    }
}
