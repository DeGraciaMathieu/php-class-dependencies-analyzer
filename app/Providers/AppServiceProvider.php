<?php

namespace App\Providers;

use App\Core\Parser\ClassParser;
use App\Core\Parser\PhpParser\PhpParserClassParser;
use App\Domain\Ports\Aggregators\FileAggregator;
use App\Domain\Ports\Repositories\FileRepository;
use App\Infrastructure\Analyze\Adapters\Jerowork\ClassDependenciesParserAdapter;
use App\Infrastructure\Analyze\Adapters\Jerowork\NodeTraverserFactory;
use App\Infrastructure\Analyze\Adapters\Services\AnalyzerServiceAdapter;
use App\Infrastructure\Analyze\Ports\AnalyzerService;
use App\Infrastructure\Analyze\Ports\ClassDependenciesParser;
use App\Infrastructure\File\Adapters\Aggregators\FileAggregatorAdapter;
use App\Infrastructure\File\Adapters\Repositories\FileRepositoryAdapter;
use App\Infrastructure\Graph\Adapters\Cytoscape\CytoscapeNetworkBuilder;
use App\Infrastructure\Views\Adapters\SystemFileLauncherAdapter;
use App\Presenter\Analyze\Shared\Network\NetworkBuilder;
use App\Presenter\Analyze\Shared\Views\SystemFileLauncher;
use Illuminate\Support\ServiceProvider;
use PhpParser\ParserFactory;

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
        $this->app->bind(ClassParser::class, PhpParserClassParser::class);

        $this->app->bind(FileRepository::class, FileRepositoryAdapter::class);

        $this->app->bind(FileAggregator::class, FileAggregatorAdapter::class);

        $this->app->bind(AnalyzerService::class, AnalyzerServiceAdapter::class);

        $this->app->bind(NetworkBuilder::class, CytoscapeNetworkBuilder::class);

        $this->app->bind(SystemFileLauncher::class, SystemFileLauncherAdapter::class);

        $this->app->bind(ClassDependenciesParser::class, function () {
            return new ClassDependenciesParserAdapter(
                (new ParserFactory)->create(ParserFactory::PREFER_PHP7),
                new NodeTraverserFactory,
            );
        });
    }
}
