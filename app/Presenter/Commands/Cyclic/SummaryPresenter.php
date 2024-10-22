<?php

namespace App\Presenter\Commands\Cyclic;

use function Laravel\Prompts\table;
use Illuminate\Console\OutputStyle;
use App\Application\Cyclic\CyclicResponse;
use App\Application\Cyclic\CyclicPresenter;

class SummaryPresenter implements CyclicPresenter
{
    public function __construct(
        private OutputStyle $output,
    ) {}

    public function hello(): void
    {
        $this->output->writeln('❀ Cyclic Dependency Analyzer ❀');
    }

    public function present(CyclicResponse $response): void
    {
        $this->output->writeln('In progress...');

        if ($response->count === 0) {

            $this->output->writeln('No cyclic dependencies found');

            return;
        }

        table(
            ['Cycle exists between', 'Through classes'],
            $this->formatCycles($response->cycles),
        );

        $this->output->writeln('Cycles found: ' . $response->count);
    }

    public function error(string $message): void
    {
        $this->output->error($message);
    }

    private function formatCycles(array $cycles): array
    {
        return array_map(function (array $cycle) {
            return [
                'cycle' => $cycle[0] . ' <-> ' . end($cycle),
                'through' => implode(' -> ', array_slice($cycle, 1, -1)),
            ];
        }, $cycles);
    }
}
