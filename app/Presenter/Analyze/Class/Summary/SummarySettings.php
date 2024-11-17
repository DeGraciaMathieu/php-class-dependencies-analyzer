<?php

namespace App\Presenter\Analyze\Class\Summary;

class SummarySettings
{
    public function __construct(
        public readonly bool $debug = false,
        public readonly ?string $target = null,
        public readonly bool $info = false,
        public readonly bool $humanReadable = false,
    ) {}
}
