<?php

namespace App\Presenter\Analyze\Summary;

class SummaryMapper
{
    public static function from(array $metrics): array
    {
        return array_map(function ($metric) use ($metrics) {
            return [
                'name' => $metric['name'],
                'ec' => $metric['afferent'],
                'ac' => $metric['efferent'],
                'i' => $metric['instability'],
            ];
        }, $metrics);
    }
}
