<?php

namespace App\Presenter\Commands\Cyclic\Summary;

use App\Presenter\Commands\Shared\NameFormatter;

class CyclicPresenterMapper
{
    public static function from(array $cycles): array
    {
        return array_map(function (array $cycle) {
            return [
                'start' => NameFormatter::humanReadable($cycle[0]),
                'end' => NameFormatter::humanReadable(end($cycle)),
                'through' => implode(' -> ', array_map(function($item) {
                    return NameFormatter::className($item);
                }, $cycle)) . ' -> ' . NameFormatter::className($cycle[0]),
            ];
        }, $cycles);
    }
}
