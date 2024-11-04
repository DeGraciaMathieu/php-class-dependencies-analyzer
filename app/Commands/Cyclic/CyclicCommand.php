<?php

namespace App\Commands\Cyclic;

use App\Commands\AbstractCommand;
use App\Application\Cyclic\CyclicAction;
use App\Application\Cyclic\CyclicRequest;
use App\Application\Cyclic\CyclicPresenter;
use App\Commands\Cyclic\SummaryPresenterFactory;

class CyclicCommand extends AbstractCommand
{
    protected $signature = 'cyclic {path} 
        {--only=} 
        {--exclude=} 
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
            only: $this->stringToList('only'),
            exclude: $this->stringToList('exclude'),
        );
    }

    private function makePresenter(): CyclicPresenter
    {
        return app(SummaryPresenterFactory::class)->make($this);
    }
}
