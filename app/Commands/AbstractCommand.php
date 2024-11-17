<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

abstract class AbstractCommand extends Command
{
    public function optionToList(string $key): array
    {
        $value = $this->option($key);

        return $this->stringToList($value);
    }

    public function argumentToList(string $key): array
    {
        $value = $this->argument($key);

        return $this->stringToList($value);
    }

    private function stringToList(?string $value): array
    {
        return $value === null ? [] : explode(',', $value);
    }
}
