<?php

namespace App\Presenter\Analyze\Component\Summary;

use Throwable;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Analyze\Component\Summary\SummaryView;
use App\Presenter\Analyze\Component\Summary\SummaryMapper;
use App\Presenter\Analyze\Component\Summary\SummarySettings;
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
        alert('sorry, something went wrong');

        if ($this->settings->debug) {
            alert($e);
        }

        alert($e->getMessage());
    }

    public function present(AnalyzeResponse $response): void
    {
        $metrics = $response->metrics;

        $metrics = $this->transformer->apply($metrics);

        $metrics = $this->mapper->from($metrics);

        $this->view->show(new SummaryViewModel($metrics));
    }
}
