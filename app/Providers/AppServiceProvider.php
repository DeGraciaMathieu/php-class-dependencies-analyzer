<?php

namespace App\Providers;

use PhpParser\ParserFactory;
use Illuminate\Support\ServiceProvider;
use App\Domain\Ports\Aggregators\FileAggregator;
use App\Domain\Ports\Repositories\FileRepository;
use App\Presenter\Analyze\Graph\Ports\GraphMapper;
use App\Infrastructure\Analyze\Ports\AnalyzerService;
use App\Infrastructure\Analyze\Ports\ClassDependenciesParser;
use App\Infrastructure\Analyze\Services\AnalyzerServiceAdapter;
use App\Infrastructure\Analyze\Adapters\Jerowork\NodeTraverserFactory;
use App\Infrastructure\File\Adapters\Aggregators\FileAggregatorAdapter;
use App\Infrastructure\File\Adapters\Repositories\FileRepositoryAdapter;
use App\Presenter\Analyze\Graph\Adapters\Cytoscape\CytoscapeGraphMapper;
use App\Infrastructure\Analyze\Adapters\Jerowork\ClassDependenciesParserAdapter;

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

        $this->app->bind(FileAggregator::class, FileAggregatorAdapter::class);

        $this->app->bind(AnalyzerService::class, AnalyzerServiceAdapter::class);

        $this->app->bind(GraphMapper::class, CytoscapeGraphMapper::class);

        $this->app->bind(ClassDependenciesParser::class, function () {
            return new ClassDependenciesParserAdapter(
                (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
                new NodeTraverserFactory(),
            );
        });
    }
}
