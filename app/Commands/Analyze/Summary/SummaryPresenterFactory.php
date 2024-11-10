<?php

namespace App\Commands\Analyze\Summary;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Analyze\TransformerFactory;
use App\Presenter\Analyze\Summary\SummaryView;
use App\Presenter\Analyze\Summary\SummaryMapper;
use App\Presenter\Analyze\Summary\SummaryPresenter;
use App\Commands\Analyze\Summary\SummarySettingsFactory;

class SummaryPresenterFactory
{
    public function __construct(
        private readonly SummaryView $view,
        private readonly SummaryMapper $mapper,
        private readonly SummarySettingsFactory $settingsFactory,
        private readonly TransformerFactory $transformerFactory,
    ) {}

    public function make(Command $command): SummaryPresenter
    {
        return new SummaryPresenter(
            view: $this->view,
            mapper: $this->mapper,
            transformer: $this->transformerFactory->make($command),
            settings: $this->settingsFactory->make($command),
        );
    }
}
