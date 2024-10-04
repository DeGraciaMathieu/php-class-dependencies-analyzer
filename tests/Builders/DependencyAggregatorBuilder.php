<?php

namespace Tests\Builders;

use App\Domain\Entities\ClassDependencies;
use App\Domain\Aggregators\DependencyAggregator;

class DependencyAggregatorBuilder
{
    public function __construct(
        private DependencyAggregator $dependencyAggregator,
        private ClassDependenciesBuilder $classDependenciesBuilder,
    ) {}

    public function addOneClassDependencies(): DependencyAggregatorBuilder
    {
        $classDependencies = $this->classDependenciesBuilder->build();

        return $this->addClassDependencies($classDependencies);

        return $this;
    }

    public function addClassDependencies(ClassDependencies $classDependencies): self
    {
        $this->dependencyAggregator->aggregate($classDependencies);

        return $this;
    }

    public function build(): DependencyAggregator
    {
        return $this->dependencyAggregator;
    }
}
