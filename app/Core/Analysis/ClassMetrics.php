<?php

declare(strict_types=1);

namespace App\Core\Analysis;

use App\Core\Parser\ClassKind;

/**
 * Coupling metrics of a single class, computed on the full graph.
 *
 * - afferent (Ca): number of analysed classes depending on this class
 * - efferent (Ce): number of analysed classes this class depends on (internal only)
 * - externalCount: number of dependencies towards classes never analysed (vendor, native PHP...)
 * - instability (I): Ce / (Ca + Ce), 0 when Ca + Ce = 0; raw value, never rounded
 */
final readonly class ClassMetrics
{
    public function __construct(
        public string $fqcn,
        public ClassKind $kind,
        public int $afferent,
        public int $efferent,
        public int $externalCount,
        public float $instability,
    ) {}
}
