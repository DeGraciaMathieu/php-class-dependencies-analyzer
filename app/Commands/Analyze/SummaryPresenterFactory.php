<?php

namespace App\Commands\Analyze;

use App\Commands\Analyze\FilterFactory;
use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Summary\SummaryView;
use App\Commands\Analyze\SummarySettingsFactory;
use App\Presenter\Analyze\Summary\SummaryMapper;
use App\Presenter\Analyze\Summary\SummaryPresenter;

class SummaryPresenterFactory
{
    public function __construct(
        private readonly SummaryView $view,
        private readonly SummaryMapper $mapper,
        private readonly SummarySettingsFactory $settingsFactory,
        private readonly FilterFactory $filterFactory,
    ) {}

    public function make(Command $command): SummaryPresenter
    {
        return new SummaryPresenter(
            view: $this->view,
            mapper: $this->mapper,
            filter: $this->filterFactory->make($command),
            settings: $this->settingsFactory->make($command),
        );
    }
}
