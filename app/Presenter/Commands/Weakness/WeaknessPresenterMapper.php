<?php

namespace App\Presenter\Commands\Weakness;

class WeaknessPresenterMapper
{
    public static function from(array $metrics): array
    {
        return array_map(function ($metric) {
            return [
                'class' => $metric['class'],
                'instability' => $metric['class_instability'],
                'dependency' => $metric['dependency'],
                'dependency_instability' => $metric['dependency_instability'],
                'score' => $metric['score'],
            ];
        }, $metrics);
    }
}
