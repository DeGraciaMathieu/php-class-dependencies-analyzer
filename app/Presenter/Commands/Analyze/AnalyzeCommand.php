<?php

namespace App\Presenter\Commands\Analyze;

use App\Application\Analyze\AnalyzeAction;
use App\Application\Analyze\AnalyzeRequest;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Commands\Shared\AbstractCommand;
use App\Presenter\Commands\Analyze\Graph\GraphView;
use App\Presenter\Commands\Analyze\Summary\SummaryView;
use App\Presenter\Commands\Analyze\Graph\GraphPresenter;
use App\Presenter\Commands\Analyze\Filters\FilterFactory;
use App\Presenter\Commands\Analyze\Summary\SummaryMapper;
use App\Presenter\Commands\Analyze\Graph\Ports\GraphMapper;
use App\Presenter\Commands\Analyze\Summary\SummaryPresenter;
use App\Presenter\Commands\Analyze\Graph\GraphSettingsFactory;
use App\Presenter\Commands\Analyze\Summary\SummarySettingsFactory;

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
        return new GraphPresenter(
            view: app(GraphView::class),
            mapper: app(GraphMapper::class),
            filter: FilterFactory::make($this),
            settings: GraphSettingsFactory::make($this),
        );
    }

    private function makeSummaryPresenter(): SummaryPresenter
    {
        return new SummaryPresenter(
            view: app(SummaryView::class),
            mapper: app(SummaryMapper::class),
            filter: FilterFactory::make($this),
            settings: SummarySettingsFactory::make($this),
        );
    }
}
