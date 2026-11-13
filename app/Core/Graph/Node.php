<?php

declare(strict_types=1);

namespace App\Core\Graph;

use App\Core\Parser\ClassKind;

final readonly class Node
{
    public function __construct(
        public string $fqcn,
        public ClassKind $kind,
        public string $file,
    ) {}
}
