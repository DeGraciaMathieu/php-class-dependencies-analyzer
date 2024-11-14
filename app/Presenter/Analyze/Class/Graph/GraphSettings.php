<?php

namespace App\Presenter\Analyze\Class\Graph;

class GraphSettings
{
    public function __construct(
        public readonly bool $debug = false,
        public readonly ?string $target = null,
    ) {}
}
