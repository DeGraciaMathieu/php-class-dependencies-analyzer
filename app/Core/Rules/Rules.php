<?php

declare(strict_types=1);

namespace App\Core\Rules;

/**
 * Factories of the available rules, for dependencies.php files.
 */
final readonly class Rules
{
    public static function noCycles(): NoCycles
    {
        return new NoCycles;
    }

    public static function stableDependencies(float $tolerance = 0.0): StableDependencies
    {
        return new StableDependencies($tolerance);
    }

    public static function maxDistanceFromMainSequence(float $max): MaxDistanceFromMainSequence
    {
        return new MaxDistanceFromMainSequence($max);
    }
}
