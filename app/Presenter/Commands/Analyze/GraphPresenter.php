<?php

namespace App\Presenter\Commands\Analyze;

use Illuminate\Console\OutputStyle;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;

class GraphPresenter implements AnalyzePresenter
{
    public function __construct(
        private OutputStyle $output,
    ) {}

    public function hello(): void
    {
        $this->output->writeln('❀ PHP Instability Analyzer ❀');
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
