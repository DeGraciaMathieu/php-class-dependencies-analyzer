<?php

declare(strict_types=1);

namespace App\Core\Analysis;

/**
 * Robert C. Martin metrics of a component, computed on the full graph. Raw values, never rounded.
 *
 * - classes: every class of the component, traits included
 * - classCount (Nc): number of classes, traits excluded
 * - abstractCount (Na): number of interfaces and abstract classes
 * - abstractness (A): Na / Nc, null when Nc = 0 (not measurable)
 * - afferent (Ca): number of distinct classes outside the component depending on one of its classes
 * - efferent (Ce): number of distinct analysed classes outside the component one of its classes depends on
 * - instability (I): Ce / (Ca + Ce), null when Ca + Ce = 0 (isolated component, not measurable)
 * - distance (D): distance from the main sequence, |A + I - 1|, null when A or I is null
 */
final readonly class ComponentMetrics
{
    /**
     * @param  list<string>  $classes
     */
    public function __construct(
        public string $name,
        public array $classes,
        public int $classCount,
        public int $abstractCount,
        public ?float $abstractness,
        public int $afferent,
        public int $efferent,
        public ?float $instability,
        public ?float $distance,
    ) {}
}
