<?php

namespace App\Commands\Analyze\Component\Summary;

use LaravelZero\Framework\Commands\Command;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Analyze\Component\Summary\SummaryView;
use App\Presenter\Analyze\Component\Summary\SummaryMapper;
use App\Presenter\Analyze\Component\Summary\SummaryPresenter;
use App\Commands\Analyze\Component\Factories\TransformerFactory;
use App\Commands\Analyze\Component\Summary\SummarySettingsFactory;

class SummaryPresenterFactory
{
    public function __construct(
        private readonly SummaryView $view,
        private readonly SummaryMapper $mapper,
        private readonly TransformerFactory $transformerFactory,
        private readonly SummarySettingsFactory $settingsFactory,
    ) {}

    public function make(Command $command): AnalyzePresenter
    {
        return new SummaryPresenter(
            view: $this->view,
            mapper: $this->mapper,
            transformer: $this->transformerFactory->make($command),
            settings: $this->settingsFactory->make($command),
        );
    }
}
