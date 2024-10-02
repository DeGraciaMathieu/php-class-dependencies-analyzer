<?php

namespace App\Domain\Services;

use App\Domain\ValueObjects\Coupling;

class Instability
{
    public static function calculate(Coupling $afferent, Coupling $efferent): float
    {
        return $afferent->value / (($efferent->value + $afferent->value) ?: 1);
    }
}
