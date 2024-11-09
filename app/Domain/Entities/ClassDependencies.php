<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Fqcn;
use App\Domain\Services\Instability;
use App\Domain\ValueObjects\Coupling;
use App\Domain\ValueObjects\IsAbstract;
use App\Domain\ValueObjects\IsInterface;
use App\Domain\ValueObjects\Dependencies;

class ClassDependencies
{
    public Coupling $afferent;
    public Coupling $efferent;
    public float $instability = 0;
    public float $abstractness = 0;
    public int $numberOfAbstractDependencies = 0;

    public function __construct(
        public Fqcn $fqcn,
        public Dependencies $dependencies,
        public IsInterface $isInterface,
        public IsAbstract $isAbstract,
    ) {
        $this->initializeCouplings();
    }

    private function initializeCouplings(): void
    {
        $this->afferent = new Coupling($this->dependencies->count());
        $this->efferent = new Coupling(0);
    }

    public function getName(): string
    {
        return $this->fqcn->getValue();
    }

    public function getAfferent(): int
    {
        return $this->afferent->getValue();
    }

    public function getEfferent(): int
    {
        return $this->efferent->getValue();
    }

    public function getInstability(): float
    {
        return $this->instability;
    }

    public function getRoundedInstability(): float
    {
        return number_format($this->instability, 2);
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
        $this->efferent->increment();
    }

    public function hasDependency(ClassDependencies $otherClass): bool
    {
        return $this->dependencies->knows($otherClass->fqcn);
    }

    public function calculateInstability(): void
    {
        $this->instability = Instability::calculate($this->afferent, $this->efferent);
    }

    public function isAbstract(): bool
    {
        return $this->isAbstract->isTrue() || $this->isInterface->isTrue();
    }

    public function getDependencies(): array
    {
        return $this->dependencies->getValues();
    }

    public function getAbstractness(): float
    {
        return $this->abstractness;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'afferent' => $this->getAfferent(),
            'efferent' => $this->getEfferent(),
            'dependencies' => $this->getDependencies(),
            'instability' => $this->getRoundedInstability(),
            'abstractness' => $this->getAbstractness(),
            'abstract' => $this->isAbstract(),
            'number_of_abstract_dependencies' => $this->numberOfAbstractDependencies,
        ];
    }

    public function hasNoDependencies(): bool
    {
        return $this->getAfferent() === 0;
    }

    public function incrementNumberOfAbstractDependencies(): void
    {
        $this->numberOfAbstractDependencies++;
    }

    public function calculateAbstractness(): void
    {
        $this->abstractness = $this->hasNoDependencies()
            ? 0
            : $this->numberOfAbstractDependencies / $this->getAfferent();
    }
}
