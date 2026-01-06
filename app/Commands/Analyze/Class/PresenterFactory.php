<?php

namespace App\Commands\Analyze\Class;

use Illuminate\Console\Command;
use App\Application\Analyze\AnalyzePresenter;
use App\Commands\Analyze\Class\Graph\GraphPresenterFactory;
use App\Commands\Analyze\Class\Bubble\BubblePresenterFactory;
use App\Commands\Analyze\Class\Summary\SummaryPresenterFactory;

class PresenterFactory
{
    public function __construct(
        private readonly BubblePresenterFactory $bubblePresenterFactory,
        private readonly GraphPresenterFactory $graphPresenterFactory,
        private readonly SummaryPresenterFactory $summaryPresenterFactory,
    ) {}

    public function make(Command $command): AnalyzePresenter
    {
        return match ($command->option('bubble')) {
            true => $this->makeBubblePresenter($command),
            false => match ($command->option('graph')) {
                true => $this->makeGraphPresenter($command),
                false => $this->makeSummaryPresenter($command),
            },
        };
    }

    private function makeBubblePresenter(Command $command): AnalyzePresenter
    {
        return $this->bubblePresenterFactory->make($command);
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
