<?php

namespace App\Presenter\Analyze\Summary;

use Throwable;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Presenter\Analyze\Filters\Contracts\Transformer;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Analyze\Summary\SummaryView;
use App\Presenter\Analyze\Summary\SummaryMapper;
use App\Presenter\Analyze\Summary\SummarySettings;
use App\Presenter\Analyze\Summary\SummaryViewModel;

class SummaryPresenter implements AnalyzePresenter
{
    public function __construct(
        private readonly SummaryView $view,
        private readonly SummaryMapper $mapper,
        private readonly Transformer $transformer,
        private readonly SummarySettings $settings,
    ) {}

    public function hello(): void
    {
        info('❀ PHP Class Dependencies Analyzer ❀');

        info('Analyze in progress...');
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
        $metrics = $response->metrics;

        $metrics = $this->transformer->apply($metrics);

        $metrics = $this->mapper->from($metrics, $this->settings->humanReadable);

        $viewModel = new SummaryViewModel($metrics, $response->count, $this->settings);

        $this->view->show($viewModel);
    }
}
