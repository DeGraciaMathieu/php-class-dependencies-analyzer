<?php

namespace App\Presenter\Analyze\Class\Graph;

use Throwable;
use function Laravel\Prompts\info;
use function Laravel\Prompts\alert;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Analyze\Class\Graph\GraphMapper;
use App\Presenter\Analyze\Class\Graph\GraphSettings;
use App\Presenter\Analyze\Class\Graph\GraphViewModel;
use App\Presenter\Analyze\Shared\Filters\Contracts\Transformer;

class GraphPresenter implements AnalyzePresenter
{
    public function __construct(
        private readonly GraphSettings $settings,
        private readonly Transformer $transformer,
        private readonly GraphMapper $mapper,
        private readonly GraphView $view,
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

        $network = $this->mapper->from($metrics);

        $this->view->show(new GraphViewModel($network));
    }
}
