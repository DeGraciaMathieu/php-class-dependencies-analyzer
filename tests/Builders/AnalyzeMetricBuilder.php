<?php

namespace Tests\Builders;

use App\Application\Analyze\AnalyzeMetric;

class AnalyzeMetricBuilder
{
    private string $name = 'default';
    private array $dependencies = [];
    private bool $abstract = false;
    private float $efferent = 0;
    private float $afferent = 0;
    private float $instability = 0;
    private int $numberOfAbstractDependencies = 0;
    private float $ratio = 0;

    public function withName(string $value): self
    {
        $this->name = $value;

        return $this;
    }

    public function withDependencies(array $value): self
    {
        $this->dependencies = $value;

        return $this;
    }

    public function withInstability(float $value): self
    {
        $this->instability = $value;

        return $this; 
    }

    public function withAbstractnessRatio(float $value): self
    {
        $this->ratio = $value;

        return $this;
    }

    public function build(): AnalyzeMetric
    {
        return new AnalyzeMetric([
            'name' => $this->name,
            'dependencies' => $this->dependencies,
            'abstract' => $this->abstract,
            'coupling' => [
                'efferent' => $this->efferent,
                'afferent' => $this->afferent,
                'instability' => $this->instability,
            ],
            'abstractness' => [
                'numberOfAbstractDependencies' => $this->numberOfAbstractDependencies,
                'ratio' => $this->ratio,
            ],
        ]);
    }
}
