<?php

namespace App\Application\Analyze;

class AnalyzeRequest
{
    public function __construct(
        public readonly string $path,
        public readonly array $only = [],
        public readonly array $exclude = [],
    ) {}
}
