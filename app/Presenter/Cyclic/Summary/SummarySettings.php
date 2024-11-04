<?php

namespace App\Presenter\Cyclic\Summary;

class SummarySettings
{
    public function __construct(
        public readonly bool $debug = false,
    ) {}
}
