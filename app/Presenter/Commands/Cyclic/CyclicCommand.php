<?php

namespace App\Presenter\Commands\Cyclic;

use App\Application\Cyclic\CyclicAction;
use App\Application\Cyclic\CyclicRequest;
use App\Application\Cyclic\CyclicPresenter;
use LaravelZero\Framework\Commands\Command;
use App\Presenter\Commands\Cyclic\Summary\SummaryView;
use App\Presenter\Commands\Cyclic\Summary\SummaryPresenter;
use App\Presenter\Commands\Cyclic\Summary\SummarySettingsFactory;

class CyclicCommand extends Command
{
    protected $signature = 'cyclic {path} 
        {--filters=} 
        {--debug}
    ';

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
        return new SummaryPresenter(
            view: app(SummaryView::class),
            settings: SummarySettingsFactory::make($this),
        );
    }

    private function prepareFilters(): array
    {
        return explode(',', $this->option('filters'));
    }
}
