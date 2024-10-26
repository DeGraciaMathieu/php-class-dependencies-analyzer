<?php

namespace App\Presenter\Commands\Weakness\Summary;

use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Weakness\WeaknessResponse;
use App\Application\Weakness\WeaknessPresenter;
use App\Presenter\Commands\Shared\ArrayFormatter;
use App\Presenter\Commands\Weakness\Summary\SummaryView;
use App\Presenter\Commands\Weakness\Summary\WeaknessPresenterMapper;

class SummaryPresenter implements WeaknessPresenter
{
    public function __construct(
        private Settings $settings,
    ) {}

    public function hello(): void
    {
        info('❀ PHP Stable Dependencies Analyzer ❀');
    }

    public function error(string $message): void
    {
        alert('sorry, something went wrong');

        alert($message);
    }

    public function present(WeaknessResponse $response): void
    {
        $metrics = $this->applyFiltersOnMetrics($response);

        $metrics = WeaknessPresenterMapper::from($metrics);

        $viewModel = new SummaryViewModel($metrics, $response->count, $this->settings->minDelta());

        app(SummaryView::class)->show($viewModel);
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
