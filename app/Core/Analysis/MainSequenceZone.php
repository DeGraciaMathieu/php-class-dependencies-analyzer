<?php

declare(strict_types=1);

namespace App\Core\Analysis;

/**
 * Corners of the A/I chart far from the main sequence (distance D above 0.5):
 * - pain: stable and concrete (A + I < 0.5), hard to change while many depend on it
 * - uselessness: unstable and abstract (A + I > 1.5), abstractions nobody depends on
 */
enum MainSequenceZone: string
{
    case Pain = 'pain';
    case Uselessness = 'uselessness';

    private const MAX_DISTANCE = 0.5;

    public static function of(ComponentMetrics $component): ?self
    {
        if ($component->abstractness === null || $component->instability === null) {
            return null;
        }

        $offset = $component->abstractness + $component->instability - 1;

        return match (true) {
            $offset < -self::MAX_DISTANCE => self::Pain,
            $offset > self::MAX_DISTANCE => self::Uselessness,
            default => null,
        };
    }
}
