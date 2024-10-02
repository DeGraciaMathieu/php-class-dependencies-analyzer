<?php

namespace App\Infrastructure\Aggregators;

use Generator;
use App\Domain\Aggregators\DependencyAggregator;
use App\Domain\Ports\Aggregators\FileAggregator;
use App\Infrastructure\Services\AnalyzerService;

class FileAggregatorAdapter implements FileAggregator
{
    private Generator $files;

    public function __construct(
        private AnalyzerService $analyzerService,
    ) {}

    public function aggregate(Generator $files): void
    {
        $this->files = $files;
    }

    public function getAllDependencies(): DependencyAggregator
    {
        $analyzeAggregator = app(DependencyAggregator::class);

        foreach ($this->files as $file) {

            $classCoupling = $this->analyzerService->getDependencies($file);

            $analyzeAggregator->aggregate($classCoupling);
        }

        return $analyzeAggregator;
    }
}
