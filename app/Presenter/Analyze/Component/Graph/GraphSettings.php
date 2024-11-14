<?php

namespace App\Presenter\Analyze\Component\Graph;

class GraphSettings
{
    public function __construct(
        public readonly array $components = [],
        public readonly bool $debug = false,
        public readonly bool $info = false,
    ) {}
}
