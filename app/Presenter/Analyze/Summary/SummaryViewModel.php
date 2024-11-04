<?php

namespace App\Presenter\Analyze\Summary;

class SummaryViewModel
{
    public function __construct(
        public readonly array $metrics,
        public readonly int $count,
        public readonly bool $debug = false,
    ) {}
}
