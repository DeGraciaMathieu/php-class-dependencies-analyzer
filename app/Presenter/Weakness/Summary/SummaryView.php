<?php

namespace App\Presenter\Weakness\Summary;

use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;
use App\Presenter\Weakness\Summary\SummaryViewModel;

class SummaryView
{
    public function show(SummaryViewModel $viewModel): void
    {
        count($viewModel->metrics) === 0
            ? warning('Good work! No weaknesses found')
            : $this->showTable($viewModel);
    }

    protected function showTable(SummaryViewModel $viewModel): void
    {
        table(
            headers: ['Class', 'Instability', 'Dependency', 'Instability', 'Delta'],
            rows: $viewModel->metrics,
        );

        $this->showHowManyWeaknessesFound($viewModel);
    }

    protected function showHowManyWeaknessesFound(SummaryViewModel $viewModel): void
    {
        if ($viewModel->delta) {
            outro(sprintf('Showing weaknesses with delta greater than %s', $viewModel->delta));
        }

        outro('A weakness is a class that depends on a class that is more unstable than itself. More the delta is high, more the dependency is unstable.');
        outro('It can be a sign of a bad design and an indicator of a class that can suffer from side effects of its dependencies.');

        outro(sprintf('Found %d weaknesses in %d classes', count($viewModel->metrics), $viewModel->totalClasses));
    }
}
