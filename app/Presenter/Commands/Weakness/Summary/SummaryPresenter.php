<?php

namespace App\Presenter\Commands\Weakness\Summary;

use Throwable;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Weakness\WeaknessResponse;
use App\Application\Weakness\WeaknessPresenter;
use App\Presenter\Commands\Shared\ArrayFormatter;
use App\Presenter\Commands\Weakness\Summary\SummaryView;
use App\Presenter\Commands\Weakness\Summary\SummarySettings;
use App\Presenter\Commands\Weakness\Summary\WeaknessPresenterMapper;

class SummaryPresenter implements WeaknessPresenter
{
    public function __construct(
        private readonly SummarySettings $settings,
        private readonly SummaryView $view
    ) {}

    public function hello(): void
    {
        info('❀ PHP Class Dependencies Analyzer ❀');
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

        $metrics = ArrayFormatter::sort('score', $metrics);

        $metrics = ArrayFormatter::cut($this->settings->limit(), $metrics);

        $metrics = ArrayFormatter::filterByMinValue('score', $this->settings->minDelta(), $metrics);

        return $metrics;
    }
}
