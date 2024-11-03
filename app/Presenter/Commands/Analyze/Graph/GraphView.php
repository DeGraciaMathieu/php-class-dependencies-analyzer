<?php

namespace App\Presenter\Commands\Analyze\Graph;

use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use Illuminate\View\Factory as View;
use App\Presenter\Commands\Analyze\Graph\GraphViewModel;

class GraphView
{
    public function __construct(
        private readonly View $view,
    ) {}

    public function show(GraphViewModel $viewModel): void
    {
        $html = $this->render($viewModel);

        $this->save($html);

        $this->open();

        $this->showInfo($viewModel);
    }

    private function render(GraphViewModel $viewModel): string
    {
        $view = $this->view->make('graph', $viewModel->graph->toArray());

        return $view->render();
    }

    private function open(): void
    {
        exec('open graph.html');
    }

    private function save(string $html): void
    {
        file_put_contents('graph.html', $html);
    }

    private function showInfo(GraphViewModel $viewModel): void
    {
        info('Graph successfully generated in graph.html');

        if ($viewModel->hasManyNodes()) {
            outro('Graph is quickly bloated with many dependencies, do not hesitate to use the --only= and --exclude= options for better readability');
            outro('See the documentation for more information');
        }
    }
}
