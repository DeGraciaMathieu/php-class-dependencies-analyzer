<?php

namespace App\Infrastructure\File\Adapters\Aggregators;

use Generator;
use App\Domain\Aggregators\DependencyAggregator;
use App\Domain\Ports\Aggregators\FileAggregator;
use App\Infrastructure\Analyze\Ports\AnalyzerService;
use App\Infrastructure\File\Adapters\DataTransferObjects\FileAdapter;

class FileAggregatorAdapter implements FileAggregator
{
    private Generator $files;

    public function __construct(
        private AnalyzerService $analyzerService,
        private DependencyAggregator $dependencyAggregator,
    ) {}

    public function aggregate(Generator $files): void
    {
        $this->files = $files;
    }

    public function getAllDependencies(): DependencyAggregator
    {
        foreach ($this->files as $file) {

            $classCoupling = $this->analyzerService->getDependencies(
                new FileAdapter($file),
            );

            $this->dependencyAggregator->aggregate($classCoupling);
        }

        return $this->dependencyAggregator;
    }
}
