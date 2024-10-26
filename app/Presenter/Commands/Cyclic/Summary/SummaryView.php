<?php

namespace App\Presenter\Commands\Cyclic\Summary;

use function Laravel\Prompts\outro;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\table;

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

        outro('Cycles found: ' . $viewModel->totalClasses);
    }
}
