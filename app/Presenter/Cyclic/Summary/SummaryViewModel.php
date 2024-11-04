<?php

namespace App\Presenter\Cyclic\Summary;

class SummaryViewModel
{
    public function __construct(
        public readonly array $metrics,
        public readonly int $totalClasses,
    ) {}
}
