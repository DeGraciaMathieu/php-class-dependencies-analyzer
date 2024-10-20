<?php

namespace App\Presenter\Commands\Analyze\Summary;

use function Laravel\Prompts\table;
use Illuminate\Console\OutputStyle;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Commands\Shared\ArrayFormatter;

class SummaryPresenter implements AnalyzePresenter
{
    public function __construct(
        private OutputStyle $output,
    ) {}

    public function hello(): void
    {
        $this->output->writeln('❀ PHP Instability Analyzer ❀');
    }

    public function present(AnalyzeResponse $response): void
    {
        $this->showTable($response);
        $this->showHowManyClassesFound($response);
    }

    public function error(string $message): void
    {
        $this->output->error($message);
    }

    private function showTable(AnalyzeResponse $response): void
    {
        $metrics = ArrayFormatter::sort('name', $response->metrics);

        table(
            headers: ['Name', 'Afferent', 'Efferent', 'Instability'],
            rows: $this->formatMetrics($metrics),
        );
    }

    private function formatMetrics(array $metrics): array
    {
        return array_map(function ($metric) {
            return [
                'name' => $metric['name'],
                'afferent' => $metric['afferent'],
                'efferent' => $metric['efferent'],
                'instability' => $metric['instability'],
            ];
        }, $metrics);
    }

    private function showHowManyClassesFound(AnalyzeResponse $response): void
    {
        $this->output->writeln(
            sprintf('Found %d classes', $response->count),
        );
    }
}
