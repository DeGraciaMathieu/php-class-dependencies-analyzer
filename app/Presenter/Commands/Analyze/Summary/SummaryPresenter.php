<?php

namespace App\Presenter\Commands\Analyze\Summary;

use Throwable;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Commands\Analyze\Summary\SummaryView;
use App\Presenter\Commands\Analyze\Summary\SummarySettings;
use App\Presenter\Commands\Analyze\Summary\SummaryViewModel;

class SummaryPresenter implements AnalyzePresenter
{
    public function __construct(
        private readonly SummaryView $view,
        private readonly SummarySettings $settings,
    ) {}

    public function hello(): void
    {
        info('❀ PHP Class Dependencies Analyzer ❀');
    }

    public function error(Throwable $e): void
    {
        if ($this->settings->debug) {
            alert($e);
        }

        alert($e->getMessage());
    }

    public function present(AnalyzeResponse $response): void
    {
        $metrics = SummaryMapper::from($response->metrics);

        $viewModel = new SummaryViewModel($metrics, $response->count);

        $this->view->show($viewModel);
    }
}
