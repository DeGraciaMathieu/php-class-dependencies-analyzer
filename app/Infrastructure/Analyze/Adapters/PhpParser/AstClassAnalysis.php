<?php

namespace App\Infrastructure\Analyze\Adapters\PhpParser;

use App\Infrastructure\Analyze\Ports\ClassAnalysis;

final class AstClassAnalysis implements ClassAnalysis
{
    public function __construct(
        private readonly string $fqcn,
        private readonly array $dependencies,
        private readonly bool $isInterface = false,
        private readonly bool $isAbstract = false,
    ) {}

    public function fqcn(): string
    {
        return $this->fqcn;
    }

    public function dependencies(): array
    {
        return $this->dependencies;
    }

    public function isInterface(): bool
    {
        return $this->isInterface;
    }

    public function isAbstract(): bool
    {
        return $this->isAbstract;
    }
}
