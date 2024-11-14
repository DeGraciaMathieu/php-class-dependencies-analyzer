<?php

namespace App\Presenter\Analyze\Shared\Filters\Transformers;

use Exception;
use App\Presenter\Analyze\Shared\Filters\Collectors\Depth;
use App\Presenter\Analyze\Shared\Filters\Collectors\Metrics;
use App\Presenter\Analyze\Shared\Filters\Contracts\Transformer;

class TargetTransformer implements Transformer
{
    private int $deep = 0;

    public function __construct(
        private readonly Depth $depth,
        private readonly Metrics $metrics,
        private readonly string $target,
        private readonly ?int $depthLimit = null,
    ) {}

    /**
     * Keep target class and all its dependencies.
     */
    public function apply(array $metrics): array
    {
        $this->metrics->set($metrics);

        $this->checkTargetPresence();

        $targetClass = $this->metrics->get($this->target);

        $this->depth->add($targetClass);

        foreach ($targetClass->dependencies() as $dependency) {
            $this->deepDive($dependency);
        }

        return $this->depth->toArray();
    }

    private function checkTargetPresence(): void
    {
        if ($this->metrics->unknown($this->target)) {
            throw new Exception('Target ' . $this->target . ' not found on metrics, try verify the target name.');
        }
    }

    private function deepDive(string $dependency): void
    {
        if ($this->shouldStop($dependency)) {
            return;
        }

        $targetClass = $this->metrics->get($dependency);

        $this->depth->add($targetClass);

        $this->incrementDeep();

        foreach ($targetClass->dependencies() as $innerDependency) {
            $this->deepDive($innerDependency);
        }

        $this->decrementDeep();
    }

    private function shouldStop(string $dependency): bool
    {
        return $this->dependencyIsAlreadyAnalyzed($dependency)
            || $this->dependencyIsUnknown($dependency)
            || $this->dependencyIsTooDeep();
    }

    private function dependencyIsAlreadyAnalyzed(string $dependency): bool
    {
        return $this->depth->has($dependency);
    }

    private function dependencyIsUnknown(string $dependency): bool
    {
        return $this->metrics->unknown($dependency);
    }

    private function dependencyIsTooDeep(): bool
    {
        return $this->depthLimit !== null && $this->deep >= ($this->depthLimit - 1) ;
    }

    private function incrementDeep(): void
    {
        $this->deep++;
    }

    private function decrementDeep(): void
    {
        $this->deep--;
    }
}
