<?php

namespace App\Presenter\Cyclic\Summary;

use App\Presenter\NameFormatter;

class CycleHelper
{
    public static function through(array $classNames): string
    {
        $readableNames = self::convertToReadableNames($classNames);

        $path = self::joinWithArrows($readableNames);

        return self::completeCircularPath($path, $classNames);
    }

    private static function convertToReadableNames(array $classNames): array
    {
        return array_map(function ($className) {
            return NameFormatter::humanReadable($className);
        }, $classNames);
    }

    private static function joinWithArrows(array $names): string
    {
        return implode(' -> ', $names);
    }

    private static function completeCircularPath(string $path, array $names): string
    {
        return $path . ' -> ' . NameFormatter::humanReadable($names[0]);
    }
}
