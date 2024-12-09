<?php

namespace App\Domain\Aggregators;

use App\Domain\Services\Cycle;
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

    public function calculateAbstractness(): void
    {
        foreach ($this->classes as $givenClass) {

            foreach ($givenClass->getDependencies() as $dependency) {

                $dependencyClass = $this->get($dependency);
                
                if ($dependencyClass && $dependencyClass->isAbstract()) {
                    $givenClass->incrementNumberOfAbstractDependencies();
                }
            }

            $givenClass->calculateAbstractness();
        }
    }

    public function detectCycles(): Cycle
    {
        return $this->cyclicDependency->detect($this->classes);
    }

    public function filter(array $only = [], array $exclude = []): void
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
