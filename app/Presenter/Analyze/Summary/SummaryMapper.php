<?php

namespace App\Presenter\Analyze\Summary;

class SummaryMapper
{
    public static function from(array $metrics): array
    {
        return array_map(function ($metric) {
            return [
                'name' => $metric['name'],
                'afferent' => $metric['afferent'],
                'efferent' => $metric['efferent'],
                'instability' => $metric['instability'],
            ];
        }, $metrics);
    }
}
