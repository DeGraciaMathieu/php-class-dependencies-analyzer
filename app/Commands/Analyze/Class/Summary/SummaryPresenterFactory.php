<?php

namespace App\Commands\Analyze\Class\Summary;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Analyze\Class\TransformerFactory;
use App\Presenter\Analyze\Class\Summary\SummaryView;
use App\Presenter\Analyze\Class\Summary\SummaryMapper;
use App\Presenter\Analyze\Class\Summary\SummaryPresenter;
use App\Commands\Analyze\Class\Summary\SummarySettingsFactory;

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
