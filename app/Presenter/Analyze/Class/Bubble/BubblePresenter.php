<?php

namespace App\Presenter\Analyze\Class\Bubble;

use Exception;
use Throwable;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;

class BubblePresenter implements AnalyzePresenter
{
    public function present(AnalyzeResponse $response): void
    {
        $metrics = $response->metrics;

        dd($metrics);

        //
    }

    public function hello(): void
    {
        throw new Exception('Not implemented');
    }

    public function error(Throwable $e): void
    {
        throw new \Exception('Not implemented');
    }
}