<?php

declare(strict_types=1);

namespace App\Core\Analysis;

use App\Core\Graph\Filter;
use InvalidArgumentException;

/**
 * Maps a class to its component.
 *
 * The first named definition whose globs match the FQCN wins, in declaration order
 * (globs follow the Filter semantics). Otherwise the component is the first $depth
 * segments of the class namespace ("\" for a class without namespace).
 */
final readonly class ComponentResolver
{
    /** @var array<string, Filter> */
    private array $filters;

    /**
     * @param  array<string, string|list<string>>  $definitions  Component name => glob(s)
     */
    public function __construct(array $definitions = [], private int $depth = 2)
    {
        if ($depth < 1) {
            throw new InvalidArgumentException("Component depth must be at least 1, [{$depth}] given.");
        }

        $filters = [];

        foreach ($definitions as $name => $globs) {
            $filters[$name] = self::toFilter($name, (array) $globs);
        }

        $this->filters = $filters;
    }

    public function resolve(string $fqcn): string
    {
        foreach ($this->filters as $name => $filter) {
            if ($filter->matches($fqcn)) {
                return $name;
            }
        }

        return $this->namespacePrefixOf($fqcn);
    }

    /**
     * An empty glob list would make Filter match every class.
     *
     * @param  list<string>  $globs
     */
    private static function toFilter(string $name, array $globs): Filter
    {
        if (array_filter($globs, static fn (string $glob): bool => $glob !== '') === []) {
            throw new InvalidArgumentException("Component [{$name}] has no glob.");
        }

        return new Filter(only: $globs);
    }

    private function namespacePrefixOf(string $fqcn): string
    {
        $namespaceSegments = array_slice(explode('\\', $fqcn), 0, -1);

        if ($namespaceSegments === []) {
            return '\\';
        }

        return implode('\\', array_slice($namespaceSegments, 0, $this->depth));
    }
}
