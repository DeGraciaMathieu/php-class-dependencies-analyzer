<?php

namespace App\Presenter\Analyze\Summary;

class SummaryMapperTmp
{
    public static function from(array $metrics): array
    {
        return array_map(function ($metric) use ($metrics) {
            return [
                'name' => $metric['name'],
                'afferent' => $metric['afferent'],
                'efferent' => $metric['efferent'],
                'instability' => $metric['instability'],
                'na' => $metric['na'],
                'abstraction' => $metric['abstraction'],
                'abstract_level' => self::getAbstractLevel($metric),
            ];
        }, $metrics);
    }

    private static function getAbstractLevel(array $metric): string
    {
        if ($metric['instability'] > 0.8 && $metric['abstraction'] > 0.8) {
            return "uselessness";
        }

        if ($metric['instability'] < 0.2 && $metric['abstraction'] < 0.2) {
            return "pain";
        }

        return "comfort";
    }
}
