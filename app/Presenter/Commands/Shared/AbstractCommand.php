<?php

namespace App\Presenter\Commands\Shared;

use LaravelZero\Framework\Commands\Command;

abstract class AbstractCommand extends Command
{
    protected function stringToList(string $key): array
    {
        $value = $this->option($key);

        return $value !== null ? explode(',', $value) : [];
    }
}
