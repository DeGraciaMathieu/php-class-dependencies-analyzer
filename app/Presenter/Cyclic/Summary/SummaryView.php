<?php

namespace App\Presenter\Cyclic\Summary;

use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;
use App\Presenter\Cyclic\Summary\SummaryViewModel;

class SummaryView
{
    public function show(SummaryViewModel $viewModel): void
    {
        count($viewModel->metrics) === 0
            ? warning('No cycles found')
            : $this->showTable($viewModel);
    }

    private function showTable(SummaryViewModel $viewModel): void
    {
        table(
            ['Origin', 'Destination', 'Cyclic Path'],
            $viewModel->metrics,
        );

        outro('A cycle is a class that depends on itself through its dependencies.');
        outro('It can be a sign of a bad design and reveal an ensemble of components difficult to maintain and evolve.');

        outro('Cycles found: ' . $viewModel->totalClasses);
    }
}
