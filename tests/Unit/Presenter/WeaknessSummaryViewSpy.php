<?php

namespace Tests\Unit\Presenter;

use App\Presenter\Weakness\Summary\SummaryView;
use App\Presenter\Weakness\Summary\SummaryViewModel;

class WeaknessSummaryViewSpy extends SummaryView
{
    public bool $showTableHasBeenCalled = false;
    public int $totalClasses = 0;
    public float $delta = 0;

    protected function showTable(SummaryViewModel $viewModel): void
    {
        $this->showTableHasBeenCalled = true;
        $this->totalClasses = $viewModel->totalClasses;
        $this->delta = $viewModel->delta;
    }
}
