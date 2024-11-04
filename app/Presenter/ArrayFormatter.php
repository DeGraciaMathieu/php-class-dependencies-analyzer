<?php

namespace App\Presenter;

class ArrayFormatter
{
    public static function sort(string $key, array $items): array
    {
        usort($items, function ($a, $b) use ($key) {
            return $b[$key] <=> $a[$key];
        });

        return $items;
    }

    public static function cut(?int $limit, array $items): array
    {
        if ($limit === null) {
            return $items;
        }

        return array_slice($items, 0, $limit);
    }

    public static function filterByMinValue(string $key, ?float $minValue, array $items): array
    {
        if ($minValue === null) {
            return $items;
        }

        $items = array_filter($items, function ($item) use ($key, $minValue) {
            return $item[$key] >= $minValue;
        });

        return array_values($items);
    }
}
