<?php

namespace App\Commands\Analyze;

use LaravelZero\Framework\Commands\Command;
use App\Commands\Analyze\ComponentSettingsFactory;
use App\Presenter\Analyze\Component\ComponentView;
use App\Presenter\Analyze\Component\ComponentMapper;
use App\Presenter\Analyze\Component\ComponentPresenter;

class ComponentPresenterFactory
{
    public function __construct(
        private readonly ComponentView $view,
        private readonly ComponentMapper $mapper,
        private readonly ComponentSettingsFactory $settingsFactory,
    ) {}

    public function make(Command $command): ComponentPresenter
    {
        return new ComponentPresenter(
            view: $this->view,
            mapper: $this->mapper,
            settings: $this->settingsFactory->make($command),
        );
    }
}
