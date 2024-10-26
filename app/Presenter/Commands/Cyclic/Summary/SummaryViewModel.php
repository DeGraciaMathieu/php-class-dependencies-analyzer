<?php

namespace App\Presenter\Commands\Cyclic\Summary;

class SummaryViewModel
{
    public function __construct(
        public readonly array $metrics,
        public readonly int $totalClasses,
    ) {}
}
