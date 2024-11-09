<?php

namespace App\Presenter\Analyze\Summary;

use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\note;
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
            headers: ['Name', 'Ac', 'Ec', 'I'],
            rows: $viewModel->metrics,
        );

        $this->showInfo($viewModel);

        outro(sprintf('Found %d classes in the given path', $viewModel->count));
    }

    private function showInfo(SummaryViewModel $viewModel): void
    {
        if ($viewModel->info) {
            note('Ac (Afferent Coupling) is the number of classes that depend on the class.');
            note('Ec (Efferent Coupling) is the number of classes that the class depends on.');
            note('I (Instability) is the instability of the class.');
            note('Class with a low instability (I close to 0) is strongly used and probably critical for the application, its business logic must be tested.');
            note('Class with a high instability (I close to 1) can suffer from side effects of its dependencies and must favor abstractions.');
            note('See the documentation for more information : https://php-quality-tools.com/class-dependencies-analyzer');
        } else {
            outro('Add --info to get more information on metrics.');
        }
    }
}
