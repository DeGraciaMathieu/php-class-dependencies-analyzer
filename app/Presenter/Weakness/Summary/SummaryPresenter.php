<?php

namespace App\Presenter\Weakness\Summary;

use Throwable;
use App\Presenter\ArrayFormatter;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Weakness\WeaknessResponse;
use App\Application\Weakness\WeaknessPresenter;
use App\Presenter\Weakness\Summary\SummaryView;
use App\Presenter\Weakness\Summary\SummarySettings;

class SummaryPresenter implements WeaknessPresenter
{
    public function __construct(
        private readonly SummarySettings $settings,
        private readonly SummaryView $view
    ) {}

    public function hello(): void
    {
        info('❀ PHP Class Dependencies Analyzer ❀');

        info('Analyze in progress...');
    }

    public function error(Throwable $exception): void
    {
        alert('sorry, something went wrong');

        if ($this->settings->debug()) {
            alert($exception);
        }

        alert($exception->getMessage());
    }

    public function present(WeaknessResponse $response): void
    {
        $metrics = $this->applyFiltersOnMetrics($response);

        $metrics = WeaknessPresenterMapper::from($metrics);

        $viewModel = new SummaryViewModel($metrics, $response->count, $this->settings->minDelta());

        $this->view->show($viewModel);
    }

    private function applyFiltersOnMetrics(WeaknessResponse $response): array
    {
        $metrics = $response->metrics;

        $metrics = ArrayFormatter::sort('delta', $metrics);

        $metrics = ArrayFormatter::cut($this->settings->limit(), $metrics);

        $metrics = ArrayFormatter::filterByMinValue('delta', $this->settings->minDelta(), $metrics);

        return $metrics;
    }
}
