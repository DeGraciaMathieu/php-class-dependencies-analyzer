<?php

namespace App\Presenter\Analyze\Class\Bubble;

use Illuminate\View\Factory as View;
use App\Presenter\Analyze\Shared\Views\SystemFileLauncher;

class BubbleView
{
    public function __construct(
        private readonly View $view,
        private readonly SystemFileLauncher $systemFileLauncher,
    ) {}

    public function show(BubbleViewModel $viewModel): void
    {
        $html = $this->render($viewModel);

        $this->systemFileLauncher->save($html);
        $this->systemFileLauncher->open();
    }

    private function render(BubbleViewModel $viewModel): string
    {
        $view = $this->view->make('bubble-page', [
            'dependencies' => $viewModel->dependencies(),
        ]);

        return $view->render();
    }
}