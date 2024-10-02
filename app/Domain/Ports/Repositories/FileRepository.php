<?php

namespace App\Domain\Ports\Repositories;

use App\Domain\Ports\Aggregators\FileAggregator;

interface FileRepository
{
    public function find(string $path): FileAggregator;
}
