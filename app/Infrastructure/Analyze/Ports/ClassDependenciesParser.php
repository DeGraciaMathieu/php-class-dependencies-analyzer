<?php

namespace App\Infrastructure\Analyze\Ports;

use App\Infrastructure\Analyze\Ports\ClassAnalysis;

interface ClassDependenciesParser
{
    public function parse(string $file): ClassAnalysis;
}
