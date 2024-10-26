<?php

namespace App\Presenter\Commands\Cyclic\Summary;

use Throwable;
use function Laravel\Prompts\alert;
use App\Application\Cyclic\CyclicResponse;
use App\Application\Cyclic\CyclicPresenter;
use App\Presenter\Commands\Cyclic\Summary\SummaryView;
use App\Presenter\Commands\Cyclic\Summary\SummarySettings;
use App\Presenter\Commands\Cyclic\Summary\CyclicPresenterMapper;

class SummaryPresenter implements CyclicPresenter
{
    public function __construct(
        private readonly SummaryView $view,
        private readonly SummarySettings $settings,
    ) {}

    public function hello(): void
    {
        info('❀ PHP Stable Dependencies Analyzer ❀');
    }

    public function error(Throwable $e): void
    {
        alert('sorry, something went wrong');

        if ($this->settings->debug) {
            alert($e);
        }

        alert($e->getMessage());
    }

    public function present(CyclicResponse $response): void
    {
        $metrics = CyclicPresenterMapper::from($response->cycles);

        $viewModel = new SummaryViewModel($metrics, $response->count);

        $this->view->show($viewModel);
    }
}
