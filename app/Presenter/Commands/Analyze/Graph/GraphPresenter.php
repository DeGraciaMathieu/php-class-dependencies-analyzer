<?php

namespace App\Presenter\Commands\Analyze\Graph;

use Illuminate\Console\OutputStyle;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Commands\Analyze\Graph\Ports\GraphService;

class GraphPresenter implements AnalyzePresenter
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
        $this->output->writeln('In progress...');

        $html = app(GraphService::class)->generate($response->metrics);

        app(GraphStorage::class)->save($html);

        $this->output->writeln('Graph generated');

        exec('open graph.html');
    }

    public function error(string $message): void
    {
        $this->output->error($message);
    }
}
