<?php

use App\Domain\Entities\ClassDependencies;
use Tests\Unit\Infrastructure\Services\FileStub;
use App\Infrastructure\Analyze\Adapters\Services\AnalyzerServiceAdapter;

it('can get dependencies', function () {

    $analyzerServiceAdapter = app(AnalyzerServiceAdapter::class);

    $dependencies = $analyzerServiceAdapter->getDependencies(new FileStub('A.php'));

    expect($dependencies)->toBeInstanceOf(ClassDependencies::class);

    expect($dependencies->toArray()['dependencies'])->toBe(
        [
            'App\Infrastructure\Services\Stubs\B',
            'App\Infrastructure\Services\Stubs\C',
            'App\Infrastructure\Services\Stubs\D',
            'App\Infrastructure\Services\Stubs\E',
            'F',
            'G',
        ]
    );
});
