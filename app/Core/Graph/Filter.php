<?php

declare(strict_types=1);

namespace App\Core\Graph;

/**
 * FQCN globs anchored at both ends: "*" matches one or more characters
 * (backslashes included), a glob without "*" is an exact match.
 * Empty globs are ignored; exclusion is applied after inclusion.
 */
final readonly class Filter
{
    /** @var list<string> */
    private array $onlyPatterns;

    /** @var list<string> */
    private array $excludePatterns;

    /**
     * @param  list<string>  $only
     * @param  list<string>  $exclude
     */
    public function __construct(array $only = [], array $exclude = [])
    {
        $this->onlyPatterns = self::toPatterns($only);
        $this->excludePatterns = self::toPatterns($exclude);
    }

    public function matches(string $fqcn): bool
    {
        if ($this->onlyPatterns !== [] && ! self::matchesAny($fqcn, $this->onlyPatterns)) {
            return false;
        }

        return ! self::matchesAny($fqcn, $this->excludePatterns);
    }

    /**
     * @param  list<string>  $globs
     * @return list<string>
     */
    private static function toPatterns(array $globs): array
    {
        $patterns = [];

        foreach ($globs as $glob) {
            if ($glob === '') {
                continue;
            }

            $escapedGlob = preg_quote($glob, '#');

            $patterns[] = '#^'.str_replace('\*', '.+', $escapedGlob).'$#';
        }

        return $patterns;
    }

    /**
     * @param  list<string>  $patterns
     */
    private static function matchesAny(string $fqcn, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $fqcn) === 1) {
                return true;
            }
        }

        return false;
    }
}
