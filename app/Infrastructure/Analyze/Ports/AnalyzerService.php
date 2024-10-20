<?php

namespace App\Infrastructure\Analyze\Ports;

use App\Infrastructure\File\Ports\File;
use App\Domain\Entities\ClassDependencies;

interface AnalyzerService
{
    public function getDependencies(File $file): ClassDependencies;
}
