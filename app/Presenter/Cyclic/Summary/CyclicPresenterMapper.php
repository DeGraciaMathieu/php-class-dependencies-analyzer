<?php

namespace App\Presenter\Cyclic\Summary;

use App\Presenter\NameFormatter;
use App\Presenter\Cyclic\Summary\CycleHelper;

class CyclicPresenterMapper
{
    public static function from(array $cycles): array
    {
        return array_map(function (array $cycle) {
            return [
                'start' => self::humanReadable($cycle[0]),
                'end' => self::humanReadable(end($cycle)),
                'through' => CycleHelper::through($cycle),
            ];
        }, $cycles);
    }

    private static function humanReadable(string $name): string
    {
        return NameFormatter::humanReadable($name);
    }
}
