<?php

namespace App\Commands\Weakness;    

use App\Commands\AbstractCommand;
use App\Application\Weakness\WeaknessAction;
use App\Application\Weakness\WeaknessRequest;
use App\Application\Weakness\WeaknessPresenter;
use App\Commands\Weakness\SummaryPresenterFactory;

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
            only: $this->optionToList('only'),
            exclude: $this->optionToList('exclude'),
        );
    }

    private function makePresenter(): WeaknessPresenter
    {
        return app(SummaryPresenterFactory::class)->make($this);
    }
}
