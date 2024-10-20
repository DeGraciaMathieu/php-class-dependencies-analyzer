<?php

namespace App\Infrastructure\File\Adapters\Repositories;

use DeGraciaMathieu\FileExplorer\FileFinder;
use App\Domain\Ports\Aggregators\FileAggregator;
use App\Domain\Ports\Repositories\FileRepository;

class FileRepositoryAdapter implements FileRepository
{
    public function __construct(
        private FileAggregator $fileAggregator,
    ) {}

    public function find(string $path): FileAggregator
    {
        $fileFinder = new FileFinder(
            basePath: base_path(),
        );

        $files = $fileFinder->getFiles($path);

        $this->fileAggregator->aggregate($files);

        return $this->fileAggregator;
    }
}
