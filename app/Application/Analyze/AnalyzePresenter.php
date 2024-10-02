<?php

namespace App\Application\Analyze;

use App\Application\Analyze\AnalyzeResponse;

interface AnalyzePresenter
{
    public function hello(): void;
    public function present(AnalyzeResponse $response): void;
    public function error(string $message): void;
}
