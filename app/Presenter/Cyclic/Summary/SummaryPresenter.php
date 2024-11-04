<?php

namespace App\Presenter\Cyclic\Summary;

use Throwable;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Cyclic\CyclicResponse;
use App\Application\Cyclic\CyclicPresenter;
use App\Presenter\Cyclic\Summary\SummaryViewModel;
use App\Presenter\Cyclic\Summary\CyclicPresenterMapper;

class SummaryPresenter implements CyclicPresenter
{
    public function __construct(
        private readonly SummaryView $view,
        private readonly SummarySettings $settings,
        private readonly CyclicPresenterMapper $mapper,
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

    public function present(CyclicResponse $response): void
    {
        $metrics = $this->mapper->from($response->cycles);

        $viewModel = new SummaryViewModel($metrics, $response->count);

        $this->view->show($viewModel);
    }
}
