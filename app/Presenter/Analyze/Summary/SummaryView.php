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
        $this->displayMetrics($viewModel);
        $this->displayInfo($viewModel);
    }

    private function displayMetrics(SummaryViewModel $viewModel): void
    {
        count($viewModel->metrics()) === 0
            ? warning('No classes found')
            : $this->showTable($viewModel);
    }

    private function showTable(SummaryViewModel $viewModel): void
    {
        table(
            headers: $viewModel->headers(),
            rows: $viewModel->metrics(),
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
        outro('A stable and concrete class is heavily used by the application and has few abstractions.');
        outro('It is probably not open to extension and it will be necessary to modify it to add behaviors.');
        outro('This class has a risky maintainability because a modification will impact many classes.');

        outro('An unstable and concrete class uses many concrete dependencies, without going through abstractions, making it harder to test.');
        outro('This class has a suffering maintainability because it will suffer from side effects of its dependencies.');
        
        outro('For more information, see the documentation: https://php-quality-tools.com/class-dependencies-analyzer');
    }

    private function showMetricsInfo(): void
    {
        table(
            headers: ['Metric', 'Description'],
            rows: [
                ['Ac (Afferent Coupling)', 'number of classes that depend on the class.'],
                ['Ec (Efferent Coupling)', 'number of classes that the class depends on.'],
                ['I (Instability)', 'instability of the class.'],
                ['Na (Number of abstractions)', 'number of abstractions (interface, abstract class) contained in the class.'],
                ['A (Abstractness)', 'ratio of abstractions to the total number of methods in the class.'],
            ],
        );

        outro('Class with a low instability (I close to 0) is strongly used and probably critical for the application, its business logic must be tested.');
        outro('Class with a high instability (I close to 1) can suffer from side effects of its dependencies and must favor abstractions.');
        outro('Class with a high abstractness (A close to 1) is totally abstract and should not have any concrete code.');
        outro('See the documentation for more information : https://php-quality-tools.com/class-dependencies-analyzer');
    }

    private function showOutro(SummaryViewModel $viewModel): void
    {
        outro('Add --info to get more information on metrics.');
        outro(sprintf('Found %d classes in the given path', $viewModel->count()));
    }
}
