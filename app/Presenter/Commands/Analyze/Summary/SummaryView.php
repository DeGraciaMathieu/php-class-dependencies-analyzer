<?php

namespace App\Presenter\Commands\Analyze\Summary;

use App\Presenter\Commands\Analyze\Summary\SummaryViewModel;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\table;

class SummaryView
{
    public function show(SummaryViewModel $viewModel): void
    {
        count($viewModel->metrics) === 0
            ? warning('No classes found')
            : $this->showTable($viewModel);
    }

    private function showTable(SummaryViewModel $viewModel): void
    {
        table(
            headers: ['Name', 'Afferent', 'Efferent', 'Instability'],
            rows: $viewModel->metrics,
        );

        outro(sprintf('Found %d classes', $viewModel->count));
    }
}
