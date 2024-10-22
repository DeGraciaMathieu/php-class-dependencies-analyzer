<?php

namespace App\Application\Cyclic;

use App\Application\Cyclic\CyclicResponse;

interface CyclicPresenter
{
    public function hello(): void;
    public function present(CyclicResponse $response): void;
    public function error(string $message): void;
}
