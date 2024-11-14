<?php

namespace App\Presenter\Analyze\Component\Summary;

use App\Presenter\Analyze\Component\Shared\ComponentMapper;

class SummaryMapper 
{
    public function __construct(
        private readonly ComponentMapper $componentMapper,
    ) {}

    public function from(array $metrics): array
    {
        $components = $this->componentMapper->from($metrics);

        return $this->format($components);
    }

    private function format(array $components): array
    {
        return array_map(function ($component) {
            return [
                'name' => $component->name(),
                'Nc' => $component->countClasses(),
                'Na' => $component->countAbstractions(),
                'A' => $component->abstractness(),
                'I' => $component->instability(),
            ];
        }, $components);
    }
}
