<?php

namespace App\Presenter\Analyze\Graph;

use Throwable;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Analyze\Graph\Ports\GraphMapper;
use App\Presenter\Analyze\Filters\Contracts\Transformer;

class GraphPresenter implements AnalyzePresenter
{
    public function __construct(
        private readonly GraphView $view,
        private readonly Transformer $transformer,
        private readonly GraphSettings $settings,
        private readonly GraphMapper $mapper,
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

        $graph = $this->mapper->make($metrics);

        $this->view->show(new GraphViewModel($graph));
    }
}
