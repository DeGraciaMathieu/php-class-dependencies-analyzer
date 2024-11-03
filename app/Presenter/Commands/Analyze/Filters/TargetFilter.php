<?php

namespace App\Presenter\Commands\Analyze\Filters;

use Exception;
use App\Presenter\Commands\Analyze\Filters\Depth;
use App\Presenter\Commands\Analyze\Filters\Metrics;
use App\Presenter\Commands\Analyze\Filters\Filter;

class TargetFilter implements Filter
{
    public function __construct(
        private readonly Depth $depth,
        private readonly Metrics $metrics,
        private readonly string $target,
    ) {}

    public function apply(array $metrics): array
    {
        $this->metrics->set($metrics);

        $this->checkTargetPresence();

        $targetClass = $this->metrics->get($this->target);

        $this->depth->add($targetClass);

        foreach ($targetClass['dependencies'] as $dependency) {
            $this->deepDive($dependency);
        }

        return $this->depth->toArray();
    }

    private function checkTargetPresence(): void
    {
        if ($this->metrics->unknown($this->target)) {
            throw new Exception('Target not found on metrics, try verify the target name.');
        }
    }

    private function deepDive(string $dependency): void
    {
        if ($this->isTimeToStop($dependency)) {
            return;
        }

        $targetClass = $this->metrics->get($dependency);

        $this->depth->add($targetClass);

        foreach ($targetClass['dependencies'] as $innerDependency) {
            $this->deepDive($innerDependency);
        }
    }

    private function isTimeToStop(string $dependency): bool
    {
        return $this->depth->has($dependency) || $this->metrics->unknown($dependency);
    }
}
