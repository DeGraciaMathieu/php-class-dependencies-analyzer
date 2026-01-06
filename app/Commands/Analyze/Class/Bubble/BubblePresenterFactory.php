<?php

namespace App\Commands\Analyze\Class\Bubble;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Class\Bubble\BubbleView;
use App\Presenter\Analyze\Class\Bubble\BubbleMapper;
use App\Presenter\Analyze\Class\Bubble\BubblePresenter;

class BubblePresenterFactory
{
    public function __construct(
        private readonly BubbleMapper $mapper,
        private readonly BubbleView $view,
    ) {}

    public function make(Command $command): BubblePresenter
    {
        return new BubblePresenter(
            mapper: $this->mapper,
            view: $this->view,
        );
    }
}
