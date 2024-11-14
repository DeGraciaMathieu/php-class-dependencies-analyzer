<?php

namespace App\Presenter\Analyze\Component\Shared;

class ComponentFactory
{
    public function make(Collector $collector): Component
    {
        return new Component(
            name: $collector->name,
            countClasses: $collector->countClasses,
            countAbstractions: $collector->countAbstractions,
            totalInstability: $collector->totalInstability,
            dependencies: $collector->dependencies,
        );
    }
}
