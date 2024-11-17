<?php

namespace App\Application\Analyze;

class AnalyzeMetric
{
    public function __construct(
        private readonly array $metric,
    ) {}

    public function name(): string
    {
        return $this->metric['name'];
    }

    public function dependencies(): array
    {
        return $this->metric['dependencies'];
    }

    public function abstract(): bool
    {
        return $this->metric['abstract'];
    }

    public function efferentCoupling(): float
    {
        return $this->metric['coupling']['efferent'];
    }

    public function afferentCoupling(): float
    {
        return $this->metric['coupling']['afferent'];
    }

    public function instability(): float
    {
        return $this->metric['coupling']['instability'];
    }

    public function numberOfAbstractDependencies(): int
    {
        return $this->metric['abstractness']['numberOfAbstractDependencies'];
    }

    public function abstractnessRatio(): float
    {
        return $this->metric['abstractness']['ratio'];
    }
}
