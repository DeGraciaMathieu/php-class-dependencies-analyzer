<?php

namespace App\Application\Weakness;

class WeaknessRequest
{
    public function __construct(
        public readonly string $path,
        public readonly array $only = [],
        public readonly array $exclude = [],
    ) {}
}