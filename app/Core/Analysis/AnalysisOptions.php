<?php

declare(strict_types=1);

namespace App\Core\Analysis;

use App\Core\Graph\Filter;

final readonly class AnalysisOptions
{
    /**
     * @param  list<string>  $paths  Files or directories, absolute or relative to $basePath
     * @param  list<string>  $excludePaths  File globs matched against paths relative to $basePath
     * @param  array<string, string|list<string>>  $components  Component name => glob(s), see ComponentResolver
     */
    public function __construct(
        public array $paths,
        public string $basePath,
        public array $excludePaths = [],
        public Filter $filter = new Filter,
        public array $components = [],
        public int $componentDepth = 2,
    ) {}
}
