<?php

namespace App\Presenter\Analyze\Class\Summary;

use Throwable;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Analyze\Class\Summary\SummaryView;
use App\Presenter\Analyze\Class\Summary\SummaryMapper;
use App\Presenter\Analyze\Class\Summary\SummarySettings;
use App\Presenter\Analyze\Class\Summary\SummaryViewModel;
use App\Presenter\Analyze\Shared\Filters\Contracts\Transformer;

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
