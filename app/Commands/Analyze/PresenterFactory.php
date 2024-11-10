<?php

namespace App\Commands\Analyze;

use Illuminate\Console\Command;
use App\Commands\AbstractCommand;
use App\Application\Analyze\AnalyzeAction;
use App\Application\Analyze\AnalyzeRequest;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Analyze\Graph\GraphPresenter;
use App\Presenter\Analyze\Summary\SummaryPresenter;
use App\Commands\Analyze\Graph\GraphPresenterFactory;
use App\Commands\Analyze\Summary\SummaryPresenterFactory;

class PresenterFactory
{
    public function make(Command $command): AnalyzePresenter
    {
        return $command->option('graph')
            ? $this->makeGraphPresenter($command)
            : $this->makeSummaryPresenter($command);
    }

    private function makeGraphPresenter(Command $command): GraphPresenter
    {
        return app(GraphPresenterFactory::class)->make($command);
    }

    private function makeSummaryPresenter(Command $command): SummaryPresenter
    {
        return app(SummaryPresenterFactory::class)->make($command);
    }
}
