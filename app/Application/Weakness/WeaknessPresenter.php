<?php

namespace App\Application\Weakness;

use Throwable;
use App\Application\Weakness\WeaknessResponse;

interface WeaknessPresenter
{
    public function hello(): void;
    public function present(WeaknessResponse $response): void;
    public function error(Throwable $exception): void;
}
