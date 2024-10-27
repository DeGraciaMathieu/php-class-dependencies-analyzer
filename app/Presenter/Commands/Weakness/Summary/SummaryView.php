<?php

namespace App\Presenter\Commands\Weakness\Summary;

use function Laravel\Prompts\table;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\warning;
use App\Presenter\Commands\Weakness\Summary\SummaryViewModel;

class SummaryView
{
    public function show(SummaryViewModel $viewModel): void
    {
        count($viewModel->metrics) === 0
            ? warning('Good work! No weaknesses found')
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

        outro('A weakness is a class that depends on a class that is more unstable than it.');
        outro('It can be a sign of a bad design and an indicator of a class that can suffer from side effects of its dependencies.');

        outro(sprintf('Found %d weaknesses in %d classes', count($viewModel->metrics), $viewModel->totalClasses));
    }
}
