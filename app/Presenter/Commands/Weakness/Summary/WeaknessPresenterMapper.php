<?php

namespace App\Presenter\Commands\Weakness\Summary;

use App\Presenter\Commands\Shared\NameFormatter;

class WeaknessPresenterMapper
{
    public static function from(array $metrics): array
    {
        return array_map(function ($metric) {
            return [
                'class' => NameFormatter::humanReadable($metric['class']),
                'instability' => $metric['class_instability'],
                'dependency' => NameFormatter::humanReadable($metric['dependency']),
                'dependency_instability' => $metric['dependency_instability'],
                'score' => $metric['score'],
            ];
        }, $metrics);
    }
}
