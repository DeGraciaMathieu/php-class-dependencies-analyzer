<?php

namespace App\Infrastructure\Analyze\Adapters\Jerowork\DataTransferObjects;

use App\Infrastructure\Analyze\Ports\ClassAnalysis;
use App\Infrastructure\Analyze\Adapters\Jerowork\Collectors\ClassTypeCollector;
use Jerowork\ClassDependenciesParser\ClassDependencies as ClassDependenciesCollector;

class ClassAnalysisAdapter implements ClassAnalysis
{
    public function __construct(
        private ClassDependenciesCollector $classDependencies,
        private ClassTypeCollector $classType,
    ) {}

    public function fqcn(): string
    {
        return $this->classDependencies->getFqn() ?? '';
    }

    public function dependencies(): array
    {
        return $this->classDependencies->getDependencyList();
    }

    public function isInterface(): bool
    {
        return $this->classType->isInterface();
    }

    public function isAbstract(): bool
    {
        return $this->classType->isAbstract();
    }

    public static function fromArray(array $attributes): self
    {
        return new self(
            $attributes['dependencies'],
            $attributes['type'],
        );
    }
}
