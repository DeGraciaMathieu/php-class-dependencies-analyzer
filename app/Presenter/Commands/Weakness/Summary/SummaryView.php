<?php

namespace App\Presenter\Commands\Weakness\Summary;

use function Laravel\Prompts\info;
use function Laravel\Prompts\table;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\warning;
use App\Presenter\Commands\Weakness\Summary\SummaryViewModel;

class SummaryView
{
    public function show(SummaryViewModel $viewModel): void
    {
        count($viewModel->metrics) === 0
            ? warning('No weaknesses found')
            : $this->showTable($viewModel);
    }

    private function showTable(SummaryViewModel $viewModel): void
    {
        table(
            headers: ['Class', 'Instability', 'Dependency', 'Instability', 'Delta'],
            rows: $viewModel->metrics,
        );

        $this->showHowManyWeaknessesFound($viewModel);
    }
    
    private function showHowManyWeaknessesFound(SummaryViewModel $viewModel): void
    {
        if ($viewModel->delta) {
            outro(sprintf('Showing weaknesses with delta greater than %s', $viewModel->delta));
        }

        outro(sprintf('Found %d weaknesses in %d classes', count($viewModel->metrics), $viewModel->totalClasses));
    }
}
