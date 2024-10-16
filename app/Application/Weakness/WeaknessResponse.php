<?php

namespace App\Application\Weakness;

class WeaknessResponse
{
    public function __construct(
        public readonly int $count,
        public readonly array $metrics,
    ) {}
}