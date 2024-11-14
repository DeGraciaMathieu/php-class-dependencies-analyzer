<?php

namespace App\Presenter\Analyze\Component\Summary;

class SummarySettings
{
    public function __construct(
        public readonly array $components,
        public readonly bool $info,
        public readonly bool $debug,
    ) {}
}
