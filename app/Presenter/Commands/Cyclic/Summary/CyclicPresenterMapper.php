<?php

namespace App\Presenter\Commands\Cyclic\Summary;

use App\Presenter\Commands\Shared\NameFormatter;

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
