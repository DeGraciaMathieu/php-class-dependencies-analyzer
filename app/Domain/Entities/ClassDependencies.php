<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Fqcn;
use App\Domain\Services\Instability;
use App\Domain\ValueObjects\Coupling;
use App\Domain\ValueObjects\Dependencies;

class ClassDependencies
{
    public Coupling $afferent;
    public Coupling $efferent;
    public float $instability = 0;

    public function __construct(
        public Fqcn $fqcn,
        public Dependencies $dependencies,
    ) {
        $this->afferent = new Coupling($dependencies->count());
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
        return number_format($this->instability, 3);
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

    public function getDependencies(): array
    {
        return $this->dependencies->getValues();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'afferent' => $this->getAfferent(),
            'efferent' => $this->getEfferent(),
            'instability' => $this->getRoundedInstability(),
            'dependencies' => $this->getDependencies(),
        ];
    }
}
