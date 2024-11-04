<?php

namespace App\Commands\Analyze; 

use App\Commands\AbstractCommand;
use App\Application\Analyze\AnalyzeAction;
use App\Application\Analyze\AnalyzeRequest;
use App\Application\Analyze\AnalyzePresenter;
use App\Commands\Analyze\GraphPresenterFactory;
use App\Presenter\Analyze\Graph\GraphPresenter;
use App\Commands\Analyze\SummaryPresenterFactory;
use App\Presenter\Analyze\Summary\SummaryPresenter;

class AnalyzeCommand extends AbstractCommand
{
    protected $signature = 'analyze {path} 
        {--graph} 
        {--only=} 
        {--exclude=} 
        {--target=}
        {--debug}
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
        return $this->option('graph') 
            ? $this->makeGraphPresenter() 
            : $this->makeSummaryPresenter();
    }

    private function makeGraphPresenter(): GraphPresenter
    {
        return app(GraphPresenterFactory::class)->make($this);
    }

    private function makeSummaryPresenter(): SummaryPresenter
    {
        return app(SummaryPresenterFactory::class)->make($this);
    }
}
