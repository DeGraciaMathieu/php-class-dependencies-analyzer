<?php

namespace App\Presenter\Commands\Analyze\Graph;

use Throwable;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Commands\Analyze\Graph\GraphViewModel;
use App\Presenter\Commands\Analyze\Graph\Ports\GraphMapper;

class GraphPresenter implements AnalyzePresenter
{
    public function __construct(
        private readonly GraphView $view,
        private readonly GraphSettings $settings,
    ) {}

    public function hello(): void
    {
        info('❀ PHP Class Dependencies Analyzer ❀');
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
        $graph = app(GraphMapper::class)->make($response->metrics);

        $viewModel = new GraphViewModel($graph);

        $this->view->show($viewModel);
    }
}
