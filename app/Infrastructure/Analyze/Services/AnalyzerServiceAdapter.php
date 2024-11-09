<?php

namespace App\Infrastructure\Analyze\Services;

use App\Domain\ValueObjects\Fqcn;
use App\Domain\ValueObjects\IsAbstract;
use App\Infrastructure\File\Ports\File;
use App\Domain\ValueObjects\IsInterface;
use App\Domain\ValueObjects\Dependencies;
use App\Domain\Entities\ClassDependencies;
use App\Infrastructure\Analyze\Ports\AnalyzerService;
use App\Infrastructure\Analyze\Ports\ClassDependenciesParser;

class AnalyzerServiceAdapter implements AnalyzerService
{
    public function __construct(
        private ClassDependenciesParser $classDependenciesParser,
    ) {}

    public function getDependencies(File $file): ClassDependencies
    {
        $classAnalysis = $this->classDependenciesParser->parse($file->fullPath());

        return new ClassDependencies(
            fqcn: Fqcn::fromString($classAnalysis->fqcn()),
            dependencies: Dependencies::fromArray($classAnalysis->dependencies()),
            isInterface: IsInterface::fromBool($classAnalysis->isInterface()),
            isAbstract: IsAbstract::fromBool($classAnalysis->isAbstract()),
        );
    }
}
