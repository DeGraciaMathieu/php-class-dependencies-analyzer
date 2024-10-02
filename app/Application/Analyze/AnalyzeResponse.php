<?php

namespace App\Application\Analyze;

class AnalyzeResponse
{
    public function __construct(
        public readonly int $count,
        public readonly array $metrics,
    ) {}
}
