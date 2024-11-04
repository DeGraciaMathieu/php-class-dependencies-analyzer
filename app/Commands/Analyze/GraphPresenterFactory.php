<?php

namespace App\Commands\Analyze;

use App\Commands\Analyze\FilterFactory;
use App\Presenter\Analyze\Graph\GraphView;
use LaravelZero\Framework\Commands\Command;
use App\Commands\Analyze\GraphSettingsFactory;
use App\Presenter\Analyze\Graph\GraphPresenter;
use App\Presenter\Analyze\Graph\Ports\GraphMapper;

class GraphPresenterFactory
{
    public function __construct(
        private readonly GraphView $view,
        private readonly GraphMapper $mapper,
        private readonly GraphSettingsFactory $settingsFactory,
        private readonly FilterFactory $filterFactory,
    ) {}

    public function make(Command $command): GraphPresenter
    {
        return new GraphPresenter(
            view: $this->view,
            mapper: $this->mapper,
            filter: $this->filterFactory->make($command),
            settings: $this->settingsFactory->make($command),
        );
    }
}
