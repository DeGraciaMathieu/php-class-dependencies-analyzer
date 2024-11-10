<?php

namespace App\Commands\Analyze\Graph;

use App\Presenter\Analyze\Graph\GraphView;
use LaravelZero\Framework\Commands\Command;
use App\Commands\Analyze\TransformerFactory;
use App\Presenter\Analyze\Graph\GraphPresenter;
use App\Presenter\Analyze\Graph\Ports\GraphMapper;
use App\Commands\Analyze\Graph\GraphSettingsFactory;

class GraphPresenterFactory
{
    public function __construct(
        private readonly GraphView $view,
        private readonly GraphMapper $mapper,
        private readonly GraphSettingsFactory $settingsFactory,
        private readonly TransformerFactory $transformerFactory,
    ) {}

    public function make(Command $command): GraphPresenter
    {
        return new GraphPresenter(
            view: $this->view,
            mapper: $this->mapper,
            transformer: $this->transformerFactory->make($command),
            settings: $this->settingsFactory->make($command),
        );
    }
}
