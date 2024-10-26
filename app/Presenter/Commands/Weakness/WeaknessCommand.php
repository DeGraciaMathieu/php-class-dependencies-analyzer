<?php

namespace App\Presenter\Commands\Weakness;

use LaravelZero\Framework\Commands\Command;
use App\Application\Weakness\WeaknessAction;
use App\Application\Weakness\WeaknessRequest;
use App\Application\Weakness\WeaknessPresenter;
use App\Presenter\Commands\Weakness\Summary\SummaryView;
use App\Presenter\Commands\Weakness\Summary\SummaryPresenter;
use App\Presenter\Commands\Weakness\Summary\SummarySettingsFactory;

class WeaknessCommand extends Command
{
    protected $signature = 'weakness {path} 
        {--filters=} 
        {--limit=} 
        {--min-delta=}
        {--debug}
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

    private function prepareFilters(): array
    {
        return explode(',', $this->option('filters'));
    }

    private function makePresenter(): WeaknessPresenter
    {
        return new SummaryPresenter(
            view: app(SummaryView::class),
            settings: SummarySettingsFactory::make($this),
        );
    }
}
