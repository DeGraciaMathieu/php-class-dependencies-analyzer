<?php

namespace App\Infrastructure\Services;

use App\Domain\Entities\ClassDependencies;
use App\Domain\ValueObjects\Fqcn;
use App\Domain\ValueObjects\Dependencies;
use DeGraciaMathieu\FileExplorer\File;
use Jerowork\ClassDependenciesParser\PhpParser\PhpParserClassDependenciesParser;

class AnalyzerService
{
    public function __construct(
        private PhpParserClassDependenciesParser $classDependenciesParser,
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
