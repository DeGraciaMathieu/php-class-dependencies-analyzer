<?php

use App\Application\Weakness\WeaknessResponse;
use Tests\Unit\Presenter\WeaknessSummaryViewSpy;
use App\Presenter\Weakness\Summary\SummarySettings;
use App\Presenter\Weakness\Summary\SummaryPresenter;

it('can be instantiated', function () {

    $presenter = new SummaryPresenter(
        new SummarySettings(),
        $summarySpy = new WeaknessSummaryViewSpy(),
    );

    $presenter->present(new WeaknessResponse(
        1,
        [
            [
                'class' => 'ClassA',
                'class_instability' => 0.5,
                'dependency' => 'ClassB',
                'dependency_instability' => 0.8,
                'delta' => 0.3,
            ],
        ],
    ));

    expect($summarySpy->showTableHasBeenCalled)->toBeTrue();
    expect($summarySpy->totalClasses)->toBe(1);
    expect($summarySpy->delta)->toBe(0.0);
});
