<?php

namespace App\Infrastructure\Services\Stubs;

class Native
{
    public function foo(): null
    {
        array_map(function () {
            return null;
        }, []);

        return null;
    }
}
