<?php

namespace App\Presenter\Commands\Weakness;

use LaravelZero\Framework\Commands\Command;
use App\Application\Weakness\WeaknessAction;
use App\Application\Weakness\WeaknessRequest;
use App\Presenter\Commands\Weakness\SummaryPresenter;

class WeaknessCommand extends Command
{
    protected $signature = 'weakness {path} 
        {--filters=} 
        {--limit=} 
        {--min-score=0.0}
    ';

    protected $description = 'Find weaknesses dependencies in the code';

    public function handle(WeaknessAction $action): void
    {
        $action->execute(
            request: $this->makeRequest(),
            presenter: $this->makePresenter(),
        );
    }

    private function makeRequest(): WeaknessRequest
    {
        return new WeaknessRequest(
            path: $this->argument('path'),
            filters: $this->prepareFilters(),
        );
    }

    private function makePresenter(): SummaryPresenter
    {
        return new SummaryPresenter(
            output: $this->output,
            limit: $this->option('limit'),
            minScore: $this->option('min-score'),
        );
    }

    private function prepareFilters(): array
    {
        return explode(',', $this->option('filters'));
    }
}
