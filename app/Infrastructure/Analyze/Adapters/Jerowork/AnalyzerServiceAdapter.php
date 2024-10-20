<?php

namespace App\Infrastructure\Analyze\Adapters\Jerowork;

use App\Domain\ValueObjects\Fqcn;
use App\Infrastructure\File\Ports\File;
use App\Domain\ValueObjects\Dependencies;
use App\Domain\Entities\ClassDependencies;
use App\Infrastructure\Analyze\Ports\AnalyzerService;
use App\Infrastructure\Analyze\Adapters\Jerowork\CustomClassDependenciesParser;

class AnalyzerServiceAdapter implements AnalyzerService
{
    public function __construct(
        private CustomClassDependenciesParser $classDependenciesParser,
    ) {}

    public function getDependencies(File $file): ClassDependencies
    {
        $classDependencies = $this->classDependenciesParser->parse($file->fullPath());

        return new ClassDependencies(
            new Fqcn($classDependencies->getFqn() ?? ''),
            new Dependencies($classDependencies->getDependencyList()),
        );
    }
}
