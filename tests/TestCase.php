<?php

namespace Tests;

use Tests\Builders\ClassDependenciesBuilder;
use Tests\Builders\DependencyAggregatorBuilder;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function oneClassDependencies(): ClassDependenciesBuilder
    {
        return new ClassDependenciesBuilder();
    }

    public function oneDependencyAggregator(): DependencyAggregatorBuilder
    {
        return app(DependencyAggregatorBuilder::class);
    }
}
