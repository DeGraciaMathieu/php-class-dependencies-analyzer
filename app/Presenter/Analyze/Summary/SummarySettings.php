<?php

namespace App\Presenter\Analyze\Summary;

class SummarySettings
{
    public function __construct(
        public readonly bool $debug = false,
        public readonly ?string $target = null,
    ) {}
}