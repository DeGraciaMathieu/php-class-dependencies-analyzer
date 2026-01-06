<?php

namespace App\Presenter\Analyze\Class\Bubble;

class BubbleViewModel
{
    public function __construct(
        public readonly array $dependencies,
    ) {}

    public function dependencies(): array
    {
        return $this->dependencies;
    }
}