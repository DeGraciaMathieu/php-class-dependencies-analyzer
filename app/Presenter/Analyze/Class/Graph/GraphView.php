<?php

namespace App\Presenter\Analyze\Class\Graph;

use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use Illuminate\View\Factory as View;
use App\Presenter\Analyze\Class\Graph\GraphViewModel;
use App\Presenter\Analyze\Shared\Views\SystemFileLauncher;

class GraphView
{
    public function __construct(
        private readonly View $view,
        private readonly SystemFileLauncher $systemFileLauncher,
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
        $view = $this->view->make('class-graph', [
            'nodes' => $viewModel->nodes(),
            'edges' => $viewModel->edges(),
        ]);

        return $view->render();
    }

    private function open(): void
    {
        $this->systemFileLauncher->open();
    }

    private function save(string $html): void
    {
        $this->systemFileLauncher->save($html);
    }

    private function showInfo(GraphViewModel $viewModel): void
    {
        info('Graph successfully generated in graph.html');

        if ($viewModel->hasManyNodes()) {
            outro('Graph is quickly bloated with many dependencies, do not hesitate to use the --only= and --exclude= options for better readability');
            outro('See the documentation for more information : https://php-quality-tools.com/class-dependencies-analyzer');
        }
    }
}
