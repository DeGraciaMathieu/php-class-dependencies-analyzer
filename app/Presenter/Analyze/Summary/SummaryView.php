<?php

namespace App\Presenter\Analyze\Summary;

use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;
use App\Presenter\Analyze\Summary\SummaryViewModel;

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

        outro('Class with a low instability (close to 0) can be important and critical for the application, it must be strongly tested.');
        outro('Class with a high instability (close to 1) can suffer from side effects of its dependencies and must favor abstractions.');

        outro('See the documentation for more information : https://php-quality-tools.com/class-dependencies-analyzer');

        outro(sprintf('Found %d classes in the given path', $viewModel->count));
    }
}
