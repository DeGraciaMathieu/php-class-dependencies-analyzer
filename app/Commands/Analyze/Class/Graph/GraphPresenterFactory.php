<?php

namespace App\Commands\Analyze\Class\Graph;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Class\Graph\GraphView;
use App\Commands\Analyze\Class\TransformerFactory;
use App\Presenter\Analyze\Class\Graph\GraphMapper;
use App\Presenter\Analyze\Class\Graph\GraphPresenter;
use App\Commands\Analyze\Class\Graph\GraphSettingsFactory;

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
