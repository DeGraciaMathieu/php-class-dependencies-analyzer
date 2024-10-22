<?php

namespace App\Presenter\Commands\Cyclic;

use App\Application\Cyclic\CyclicRequest;
use App\Application\Cyclic\CyclicPresenter;
use App\Application\Cyclic\CyclicAction;
use LaravelZero\Framework\Commands\Command;
use App\Presenter\Commands\Cyclic\SummaryPresenter;

class CyclicCommand extends Command
{
    protected $signature = 'cyclic {path} {--filters=}';

    protected $description = 'Detect cyclic dependencies in the given path';

    public function handle(CyclicAction $action): void
    {
        $action->execute(
            request: $this->makeRequest(),
            presenter: $this->makePresenter(),
        );
    }

    private function makeRequest(): CyclicRequest
    {
        return new CyclicRequest(
            path: $this->argument('path'),
            filters: $this->prepareFilters(),
        );
    }

    private function makePresenter(): CyclicPresenter
    {
        return new SummaryPresenter($this->output);
    }

    private function prepareFilters(): array
    {
        return explode(',', $this->option('filters'));
    }
}
