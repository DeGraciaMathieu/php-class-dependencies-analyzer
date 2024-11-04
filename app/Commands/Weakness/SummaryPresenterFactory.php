<?php

namespace App\Commands\Weakness;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Weakness\Summary\SummaryView;
use App\Commands\Weakness\SummarySettingsFactory;
use App\Presenter\Weakness\Summary\SummaryPresenter;

class SummaryPresenterFactory
{
    public function __construct(
        private readonly SummaryView $view,
        private readonly SummarySettingsFactory $settingsFactory,
    ) {}

    public function make(Command $command): SummaryPresenter
    {
        return new SummaryPresenter(
            view: $this->view,
            settings: $this->settingsFactory->make($command),
        );
    }
}
