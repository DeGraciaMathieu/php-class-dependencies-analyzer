<?php

declare(strict_types=1);

namespace App\Presenter\Analyze\Class\Bubble;

use Throwable;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;

class BubblePresenter implements AnalyzePresenter
{
    public function __construct(
        private readonly BubbleMapper $mapper,
        private readonly BubbleView $view,
    ) {}

    public function present(AnalyzeResponse $response): void
    {
        $metrics = $response->metrics;

        $dependencies = $this->mapper->from($metrics);

        $viewModel = new BubbleViewModel($dependencies);

        $this->view->show($viewModel);
    }

    public function hello(): void
    {
    }

    public function error(Throwable $e): void
    {
    }
}