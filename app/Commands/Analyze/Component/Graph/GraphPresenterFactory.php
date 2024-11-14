<?php

namespace App\Commands\Analyze\Component\Graph;

use LaravelZero\Framework\Commands\Command;
use App\Application\Analyze\AnalyzePresenter;
use App\Presenter\Analyze\Component\Graph\GraphView;
use App\Presenter\Analyze\Component\Graph\GraphMapper;
use App\Presenter\Analyze\Component\Graph\GraphPresenter;
use App\Commands\Analyze\Component\Graph\GraphSettingsFactory;
use App\Commands\Analyze\Component\Factories\TransformerFactory;

class GraphPresenterFactory
{
    public function __construct(
        private readonly GraphView $view,
        private readonly GraphMapper $mapper,
        private readonly TransformerFactory $transformerFactory,
        private readonly GraphSettingsFactory $settingsFactory,
    ) {}

    public function make(Command $command): AnalyzePresenter
    {
        return new GraphPresenter(
            view: $this->view,
            mapper: $this->mapper,
            transformer: $this->transformerFactory->make($command),
            settings: $this->settingsFactory->make($command),
        );
    }
}
