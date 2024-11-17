<?php

namespace App\Presenter\Analyze\Component\Summary;

use App\Presenter\Analyze\Component\Summary\SummaryViewModel;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class SummaryView
{
    public function show(SummaryViewModel $viewModel): void
    {
        $this->displayMetrics($viewModel);
        $this->displayInfo($viewModel);
    }

    private function displayMetrics(SummaryViewModel $viewModel): void
    {
        $viewModel->hasComponents()
            ? $this->showComponents($viewModel)
            : warning('No classes found');
    }

    private function showComponents(SummaryViewModel $viewModel): void
    {
        table(
            headers: $viewModel->headers(),
            rows: $viewModel->components(),
        );
    }

    private function displayInfo(SummaryViewModel $viewModel): void
    {
        $viewModel->needInfo()
            ? $this->showInfo($viewModel)
            : $this->showOutro($viewModel);
    }

    private function showInfo(SummaryViewModel $viewModel): void
    {
        $viewModel->isHumanReadable()
            ? $this->showHumanReadableInfo()
            : $this->showMetricsInfo();
    }

    private function showHumanReadableInfo(): void
    {
        outro('For more information, see the documentation: https://php-quality-tools.com/class-dependencies-analyzer');
    }

    private function showMetricsInfo(): void
    {
        outro('Try --human-readable to get a more human readable output.');
        outro('See the documentation for more information : https://php-quality-tools.com/class-dependencies-analyzer');
    }

    private function showOutro(SummaryViewModel $viewModel): void
    {
        outro('Add --info to get more information on metrics.');
        outro(sprintf('Found %d classes in the given path', $viewModel->count()));
    }
}
