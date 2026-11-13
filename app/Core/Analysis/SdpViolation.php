<?php

declare(strict_types=1);

namespace App\Core\Analysis;

/**
 * Stable Dependencies Principle violation: a component depends on a less stable one.
 *
 * - delta: toInstability - fromInstability, always > 0
 */
final readonly class SdpViolation
{
    public function __construct(
        public string $from,
        public float $fromInstability,
        public string $to,
        public float $toInstability,
        public float $delta,
    ) {}
}
