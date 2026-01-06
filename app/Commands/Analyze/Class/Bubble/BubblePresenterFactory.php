<?php

namespace App\Commands\Analyze\Class\Bubble;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Class\Bubble\BubblePresenter;
use App\Presenter\Analyze\Class\Bubble\BubbleMapper;

class BubblePresenterFactory
{
    public function __construct(
        private readonly BubbleMapper $mapper,
    ) {}

    public function make(Command $command): BubblePresenter
    {
        return new BubblePresenter(
            mapper: $this->mapper,
        );
    }
}
