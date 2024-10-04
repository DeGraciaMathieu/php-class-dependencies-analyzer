<?php

namespace App\Providers;

use PhpParser\ParserFactory;
use Illuminate\Support\ServiceProvider;
use App\Domain\Ports\Repositories\FileRepository;
use App\Infrastructure\Services\NodeTraverserFactory;
use App\Infrastructure\Repositories\FileRepositoryAdapter;
use App\Infrastructure\Services\CustomClassDependenciesParser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FileRepository::class, FileRepositoryAdapter::class);

        $this->app->bind(CustomClassDependenciesParser::class, function () {
            return new CustomClassDependenciesParser(
                (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
                new NodeTraverserFactory(),
            );
        });
    }
}
