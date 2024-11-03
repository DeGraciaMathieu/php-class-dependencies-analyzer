<?php

namespace App\Presenter\Commands\Analyze\Graph;

class GraphSettings
{
    public function __construct(
        public readonly bool $debug = false,
        public readonly ?string $target = null,
    ) {}
}
