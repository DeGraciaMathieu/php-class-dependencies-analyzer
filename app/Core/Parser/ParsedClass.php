<?php

declare(strict_types=1);

namespace App\Core\Parser;

final readonly class ParsedClass
{
    /**
     * @param  list<string>  $dependencies  Deduplicated FQCNs, without leading backslash nor the class itself
     */
    public function __construct(
        public string $fqcn,
        public ClassKind $kind,
        public array $dependencies,
        public string $file,
    ) {}
}
