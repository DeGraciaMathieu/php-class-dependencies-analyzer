<?php

namespace Tests\Unit\Infrastructure\Services;

use App\Infrastructure\File\Ports\File;

class FileStub implements File
{
    public function __construct(
        private string $path,
    ) {}

    public function fullPath(): string
    {
        return __DIR__ . '/Stubs/' . $this->path;
    }
}
