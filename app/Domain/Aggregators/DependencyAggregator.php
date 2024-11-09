<?php

namespace App\Domain\Aggregators;

use App\Domain\Services\CyclicDependency;
use App\Domain\Entities\ClassDependencies;

class DependencyAggregator
{
    private array $classes = [];

    public function __construct(
        private CyclicDependency $cyclicDependency,
    ) {}

    public function aggregate(ClassDependencies $classDependencies): void
    {
        $this->classes[$classDependencies->getName()] = $classDependencies;
    }

    public function count(): int
    {
        return count($this->classes);
    }

    public function classes(): array
    {
        return $this->classes;
    }

    public function get(string $fqcn): ?ClassDependencies
    {
        return $this->classes[$fqcn] ?? null;
    }

    public function calculateInstability(): void
    {
        foreach ($this->classes as $givenClass) {

            foreach ($this->classes as $otherClass) {

                if ($givenClass->is($otherClass)) {
                    continue;
                }

                if ($otherClass->hasDependency($givenClass)) {
                    $givenClass->incrementAfferent();
                }
            }

            $givenClass->calculateInstability();
        }
    }

    public function calculateAbstraction(): void
    {
        foreach ($this->classes as $givenClass) {

            $abstractDependencies = 0;

            $afferentCount = $givenClass->getAfferent();

            if ($afferentCount === 0) {
                $givenClass->setAbstractionLevel(0);
                $givenClass->setNumberOfAbstractDependencies(0);
                continue;
            }

            foreach ($givenClass->getDependencies() as $dependency) {

                $dependencyClass = $this->get($dependency);
                
                if ($dependencyClass && $dependencyClass->isAbstract()) {
                    $abstractDependencies++;
                }
            }

            $abstraction = $abstractDependencies / $afferentCount;

            $givenClass->setAbstractionLevel($abstraction);
            $givenClass->setNumberOfAbstractDependencies($abstractDependencies);
        }
    }

    public function detectCycles(): array
    {
        return $this->cyclicDependency->detect($this->classes);
    }

    public function filterClasses(array $only = [], array $exclude = []): void
    {
        $this->classes = array_filter($this->classes, function ($givenClass) use ($only, $exclude) {

            if ($only) {
                return $givenClass->looksLike($only);
            }

            if ($exclude) {
                return ! $givenClass->looksLike($exclude);
            }

            return true;
        });
    }

    public function toArray(): array
    {
        return array_map(function (ClassDependencies $class) {
            return $class->toArray();
        }, $this->classes);
    }
}
