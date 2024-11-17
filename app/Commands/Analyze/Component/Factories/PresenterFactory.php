<?php

namespace App\Commands\Analyze\Component\Factories;

use Illuminate\Console\Command;
use App\Application\Analyze\AnalyzePresenter;
use App\Commands\Analyze\Component\Graph\GraphPresenterFactory;
use App\Commands\Analyze\Component\Summary\SummaryPresenterFactory;

class PresenterFactory
{
    public function __construct(
        private readonly GraphPresenterFactory $graphPresenterFactory,
        private readonly SummaryPresenterFactory $summaryPresenterFactory,
    ) {}

    public function make(Command $command): AnalyzePresenter
    {
        return $command->option('graph')
            ? $this->makeGraphPresenter($command)
            : $this->makeSummaryPresenter($command);
    }

    private function makeGraphPresenter(Command $command): AnalyzePresenter
    {
        return $this->graphPresenterFactory->make($command);
    }

    private function makeSummaryPresenter(Command $command): AnalyzePresenter
    {
        return $this->summaryPresenterFactory->make($command);
    }
}
