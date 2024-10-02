<?php

namespace App\Domain\Aggregators;

use App\Domain\Entities\ClassDependencies;

class DependencyAggregator
{
    private array $classes = [];

    public function aggregate(ClassDependencies $classDependencies): void
    {
        $this->classes[] = $classDependencies;
    }

    public function count(): int
    {
        return count($this->classes);
    }

    public function classes(): array
    {
        return $this->classes;
    }

    public function calculateCoupling(): void
    {
        foreach ($this->classes as $givenClass) {

            foreach ($this->classes as $otherClass) {

                if ($givenClass->is($otherClass)) {
                    continue;
                }

                if ($otherClass->knows($givenClass)) {
                    $givenClass->incrementAfferent();
                }
            }

            $givenClass->calculateInstability();
        }
    }

    public function remove(array $filters): void
    {
        $this->classes = array_filter($this->classes, function ($givenClass) use ($filters) {
            return $givenClass->looksLike($filters);
        });
    }
}
