<?php

namespace App\Commands\Cyclic;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Cyclic\Summary\SummaryView;
use App\Commands\Cyclic\SummarySettingsFactory;
use App\Presenter\Cyclic\Summary\SummaryPresenter;
use App\Presenter\Cyclic\Summary\CyclicPresenterMapper;

class SummaryPresenterFactory
{
    public function __construct(
        private readonly SummaryView $view,
        private readonly CyclicPresenterMapper $mapper,
        private readonly SummarySettingsFactory $settingsFactory,
    ) {}

    public function make(Command $command): SummaryPresenter
    {
        return new SummaryPresenter(
            view: $this->view,
            mapper: $this->mapper,
            settings: $this->settingsFactory->make($command),
        );
    }
}
