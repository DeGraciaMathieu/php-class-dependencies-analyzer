<?php

namespace App\Presenter\Commands\Analyze;

use function Laravel\Prompts\table;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use Illuminate\Console\OutputStyle;

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
        $metrics = $response->metrics;

        $metrics = $this->sortMetrics($metrics);

        table(
            headers: ['Name', 'Afferent', 'Efferent', 'Instability'],
            rows: $this->formatMetrics($metrics),
        );

        $this->output->writeln('Found ' . $response->count . ' classes');
    }

    public function error(string $message): void
    {
        $this->output->error($message);
    }

    private function sortMetrics(array $metrics): array
    {
        usort($metrics, function ($a, $b) {
            return $b['name'] <=> $a['name'];
        });

        return $metrics;
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
}
