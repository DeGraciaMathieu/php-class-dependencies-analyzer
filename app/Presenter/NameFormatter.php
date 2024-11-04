<?php

namespace App\Presenter;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NameFormatter
{
    public static function className(string $className): string
    {
        return Str::of($className)->explode('\\')->last();
    }

    public static function humanReadable(string $className): string
    {
        $exploded = Str::of($className)->explode('\\');

        if (self::isShortName($exploded)) {
            return $className;
        }

        $exploded = self::keepLastTwoNamespaces($exploded);

        return '\\' . $exploded->implode('\\');
    }

    private static function isShortName(Collection $exploded): bool
    {
        return $exploded->count() <= 2;
    }

    private static function keepLastTwoNamespaces(Collection $exploded): Collection
    {
        return $exploded->reverse()->take(2)->reverse();
    }
}
