<?php

namespace App\Infrastructure\Services;

use App\Domain\ValueObjects\Fqcn;
use DeGraciaMathieu\FileExplorer\File;
use App\Domain\ValueObjects\Dependencies;
use App\Domain\Entities\ClassDependencies;
use App\Infrastructure\Services\CustomClassDependenciesParser;

class AnalyzerService
{
    public function __construct(
        private CustomClassDependenciesParser $classDependenciesParser,
    ) {}

    public function getDependencies(File $file): ClassDependencies
    {
        $classDependencies = $this->classDependenciesParser->parse($file->fullPath);

        return new ClassDependencies(
            new Fqcn($classDependencies->getFqn() ?? ''),
            new Dependencies($classDependencies->getDependencyList()),
        );
    }
}
