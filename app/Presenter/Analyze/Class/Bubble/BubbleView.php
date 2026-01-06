<?php

namespace App\Presenter\Analyze\Class\Bubble;

class BubbleView
{
    public function show(BubbleViewModel $viewModel): void
    {
        dd($viewModel->dependencies());
    }
}