<?php

namespace App\Presenter\Analyze\Component\Graph;

use function Laravel\Prompts\info;
use Illuminate\View\Factory as View;
use App\Presenter\Analyze\Component\Graph\GraphViewModel;
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
        $view = $this->view->make('components-graph', [
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
    }
}
