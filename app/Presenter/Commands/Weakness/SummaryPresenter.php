<?php

namespace App\Presenter\Commands\Weakness;

use function Laravel\Prompts\table;
use App\Application\Weakness\WeaknessResponse;
use App\Application\Weakness\WeaknessPresenter;
use Symfony\Component\Console\Style\OutputStyle;
use App\Presenter\Commands\Shared\ArrayFormatter;
use App\Presenter\Commands\Weakness\WeaknessPresenterMapper;

class SummaryPresenter implements WeaknessPresenter
{
    public function __construct(
        private OutputStyle $output,
        private ?int $limit = 0,
        private ?float $minScore = 0.0,
    ) {}

    public function hello(): void
    {
        $this->output->writeln('❀ PHP Instability Analyzer ❀');
        $this->output->writeln('Find weaknesses dependencies in the code');
    }

    public function present(WeaknessResponse $response): void
    {
        $metrics = $this->formatMetrics($response->metrics);

        $this->showTable($metrics);
        $this->showInformationMessage($metrics);
        $this->showHowManyClassesFound($response);
    }

    public function error(string $message): void
    {
        $this->output->error($message);
    }

    private function formatMetrics(array $metrics): array
    {
        $metrics = ArrayFormatter::sort('score', $metrics);

        $metrics = ArrayFormatter::cut($this->limit, $metrics);

        $metrics = ArrayFormatter::filterByMinValue('score', $this->minScore, $metrics);

        return WeaknessPresenterMapper::from($metrics);
    }

    private function showTable(array $metrics): void
    {
        table(
            headers: ['Class', 'Instability', 'Dependency', 'Dependency Instability', 'Score'],
            rows: $metrics,
        );
    }

    private function showInformationMessage(array $metrics): void
    {
        count($metrics) === 0
            ? $this->output->writeln('No weaknesses found')
            : $this->showHowManyWeaknessesFound($metrics);
    }

    private function showHowManyWeaknessesFound(array $metrics): void
    {
        $this->output->writeln(
            sprintf('Found %d weaknesses', count($metrics)),
        );
    }

    private function showHowManyClassesFound(WeaknessResponse $response): void
    {
        $this->output->writeln(
            sprintf('Found %d classes', $response->count),
        );
    }
}
