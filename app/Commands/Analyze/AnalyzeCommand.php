<?php

namespace App\Commands\Analyze; 

use App\Commands\AbstractCommand;
use App\Application\Analyze\AnalyzeAction;
use App\Application\Analyze\AnalyzeRequest;
use App\Application\Analyze\AnalyzePresenter;

class AnalyzeCommand extends AbstractCommand
{
    protected $signature = 'analyze {path} 
        {--components=}
        {--graph} 
        {--only=} 
        {--exclude=} 
        {--target=}
        {--depth-limit=}
        {--debug}
        {--info}
        {--human-readable : Display human readable metrics}
    ';

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
            only: $this->stringToList('only'),
            exclude: $this->stringToList('exclude'),
        );
    }

    private function makePresenter(): AnalyzePresenter
    {
        return app(PresenterFactory::class)->make($this);
    }
}
