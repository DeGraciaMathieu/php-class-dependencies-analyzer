<?php

namespace App\Domain\Ports\Aggregators;

use Generator;
use App\Domain\Aggregators\DependencyAggregator;

interface FileAggregator
{
    public function aggregate(Generator $files): void;
    public function getAllDependencies(): DependencyAggregator;
}
