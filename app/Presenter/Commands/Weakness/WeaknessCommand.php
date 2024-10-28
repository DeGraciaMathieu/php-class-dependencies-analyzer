<?php

namespace App\Presenter\Commands\Weakness;

use App\Application\Weakness\WeaknessAction;
use App\Application\Weakness\WeaknessRequest;
use App\Application\Weakness\WeaknessPresenter;
use App\Presenter\Commands\Shared\AbstractCommand;
use App\Presenter\Commands\Weakness\Summary\SummaryView;
use App\Presenter\Commands\Weakness\Summary\SummaryPresenter;
use App\Presenter\Commands\Weakness\Summary\SummarySettingsFactory;

class WeaknessCommand extends AbstractCommand
{
    protected $signature = 'weakness {path} 
        {--only=} 
        {--exclude=} 
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
            only: $this->stringToList('only'),
            exclude: $this->stringToList('exclude'),
        );
    }

    private function makePresenter(): WeaknessPresenter
    {
        return new SummaryPresenter(
            view: app(SummaryView::class),
            settings: SummarySettingsFactory::make($this),
        );
    }
}
