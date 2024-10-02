<?php

namespace App\Presenter\Commands\Analyze;

use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;

class GraphPresenter implements AnalyzePresenter
{
    public function hello(): void
    {
        //
    }

    public function howManyFilesFound(int $count): void
    {
        //
    }

    public function present(AnalyzeResponse $response): void
    {
        //
    }

    public function error(string $message): void
    {
        //
    }
}
