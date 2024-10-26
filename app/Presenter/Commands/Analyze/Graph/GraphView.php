<?php

namespace App\Presenter\Commands\Analyze\Graph;

use function Laravel\Prompts\outro;
use Illuminate\Support\Facades\View;
use App\Presenter\Commands\Analyze\Graph\GraphViewModel;

class GraphView
{
    public function show(GraphViewModel $viewModel): void
    {
        $html = $this->renderHtml($viewModel);

        $this->save($html);

        $this->open();

        outro('Graph generated');
    }

    private function renderHtml(GraphViewModel $viewModel): string
    {
        $view = View::make('graph', $viewModel->graph->toArray());

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
}
