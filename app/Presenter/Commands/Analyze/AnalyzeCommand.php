<?php

namespace App\Presenter\Commands\Analyze;

use App\Application\Analyze\AnalyzeAction;
use App\Application\Analyze\AnalyzeRequest;
use LaravelZero\Framework\Commands\Command;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Commands\Analyze\GraphPresenter;
use App\Presenter\Commands\Analyze\SummaryPresenter;

class AnalyzeCommand extends Command
{
    protected $signature = 'analyze {path} {--graph} {--filters=}';

    protected $description = 'Analyze the given path';

    public function handle(AnalyzeAction $action): void
    {
        $action->execute(
            request: $this->makeRequest(),
            presenter: $this->makePresenter(),
        );
    }

    private function makeRequest(): AnalyzeRequest
    {
        return new AnalyzeRequest(
            path: $this->argument('path'), 
            filters: explode(',', $this->option('filters')),
        );
    }

    private function makePresenter(): AnalyzePresenter
    {
        return $this->option('graph') 
            ? new GraphPresenter($this->output) 
            : new SummaryPresenter($this->output);
    }
}
