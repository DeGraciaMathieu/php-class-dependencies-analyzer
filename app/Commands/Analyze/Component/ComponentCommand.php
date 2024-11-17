<?php

namespace App\Commands\Analyze\Component;

use App\Commands\AbstractCommand;
use App\Application\Analyze\AnalyzeAction;
use App\Application\Analyze\AnalyzeRequest;
use App\Application\Analyze\AnalyzePresenter;
use App\Commands\Analyze\Component\Factories\PresenterFactory;

class ComponentCommand extends AbstractCommand
{
    protected $signature = 'analyze:component {path} {components}
        {--graph} 
        {--debug}
        {--info}
        {--human-readable : Display human readable metrics}
    ';

    protected $description = '';

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
        );
    }

    private function makePresenter(): AnalyzePresenter
    {
        return app(PresenterFactory::class)->make($this);
    }
}
