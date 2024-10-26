<?php

namespace App\Presenter\Commands\Cyclic\Summary;

class SummarySettings
{
    public function __construct(
        public readonly bool $debug = false,
    ) {}
}
