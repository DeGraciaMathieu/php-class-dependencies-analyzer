<?php

namespace App\Application\Cyclic;

class CyclicRequest
{
    public function __construct(
        public readonly string $path,
        public readonly array $only = [],
        public readonly array $exclude = [],
    ) {}
}
