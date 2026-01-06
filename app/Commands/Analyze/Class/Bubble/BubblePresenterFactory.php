<?php

namespace App\Commands\Analyze\Class\Graph;

use LaravelZero\Framework\Commands\Command;
use App\Presenter\Analyze\Class\Bubble\BubblePresenter;

class BubblePresenterFactory
{
    public function __construct(
    ) {}

    public function make(Command $command): BubblePresenter
    {
        return new BubblePresenter();
    }
}
