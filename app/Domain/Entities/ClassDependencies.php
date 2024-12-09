<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Fqcn;
use App\Domain\ValueObjects\Coupling;
use App\Domain\ValueObjects\IsAbstract;
use App\Domain\ValueObjects\IsInterface;
use App\Domain\ValueObjects\Abstractness;
use App\Domain\ValueObjects\Dependencies;

class ClassDependencies
{
    public Coupling $coupling;

    public function __construct(
        public Fqcn $fqcn,
        public Dependencies $dependencies,
        public IsInterface $isInterface,
        public IsAbstract $isAbstract,
        public Abstractness $abstractness = new Abstractness(),
    ) {
        $this->initializeCouplings();
    }

    private function initializeCouplings(): void
    {
        $this->coupling = new Coupling(efferent: $this->dependencies->count());
    }

    public function getName(): string
    {
        return $this->fqcn->getValue();
    }

    public function looksLike(array $filters): bool
    {
        return $this->fqcn->looksLike($filters);
    }

    public function is(ClassDependencies $otherClass): bool
    {
        return $this->fqcn->is($otherClass->fqcn);
    }

    public function incrementAfferent(): void
    {
        $this->coupling->incrementAfferent();
    }

    public function isDependentOn(ClassDependencies $otherClass): bool
    {
        return $this->dependencies->knows($otherClass->fqcn);
    }

    public function calculateInstability(): void
    {
        $this->coupling->calculateInstability();
    }

    public function isAbstract(): bool
    {
        return $this->isAbstract->isTrue() || $this->isInterface->isTrue();
    }

    public function getDependencies(): array
    {
        return $this->dependencies->getValues();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'dependencies' => $this->getDependencies(),
            'abstract' => $this->isAbstract(),
            'coupling' => $this->coupling->toArray(),
            'abstractness' => $this->abstractness->toArray(),
        ];
    }

    public function hasNoDependencies(): bool
    {
        return $this->coupling->nobodyUsesThis();
    }

    public function incrementNumberOfAbstractDependencies(): void
    {
        $this->abstractness->increment();
    }

    public function calculateAbstractness(): void
    {
        $this->abstractness->calculate($this->coupling->efferent);
    }
}
