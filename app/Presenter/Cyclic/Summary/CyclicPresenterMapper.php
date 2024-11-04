<?php

namespace App\Presenter\Cyclic\Summary;

use App\Presenter\NameFormatter;
use App\Presenter\Cyclic\Summary\CycleHelper;

class CyclicPresenterMapper
{
    public function from(array $cycles): array
    {
        return array_map(function (array $cycle) {
            return [
                'start' => $this->humanReadable($cycle[0]),
                'end' => $this->humanReadable(end($cycle)),
                'through' => CycleHelper::through($cycle),
            ];
        }, $cycles);
    }

    private function humanReadable(string $name): string
    {
        return NameFormatter::humanReadable($name);
    }
}
