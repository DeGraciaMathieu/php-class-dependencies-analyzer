<?php

namespace App\Presenter\Analyze\Component\Shared;

use App\Application\Analyze\AnalyzeMetric;

class Collector
{
    public string $name;
    public float $totalInstability = 0;
    public int $countAbstractions = 0;
    public int $countClasses = 0;
    public array $dependencies = [];

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function collect(AnalyzeMetric $metric): void
    {
        $this->totalInstability += $metric->instability();

        if ($metric->abstract()) {
            $this->countAbstractions++;
        }

        $this->countClasses++;
    }

    public function addDependency(string $dependency): void
    {
        $this->dependencies[] = $dependency;
    }

    public function hasDependency(string $dependency): bool
    {
        return in_array($dependency, $this->dependencies, true);
    }
}
    