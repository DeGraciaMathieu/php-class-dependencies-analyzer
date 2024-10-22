<?php

namespace App\Application\Cyclic;

class CyclicResponse
{
    public function __construct(
        public readonly int $count,
        public readonly array $cycles,
    ) {}
}
