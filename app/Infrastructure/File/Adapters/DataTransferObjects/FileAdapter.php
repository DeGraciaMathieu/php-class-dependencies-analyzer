<?php

namespace App\Infrastructure\File\Adapters\DataTransferObjects;

use App\Infrastructure\File\Ports\File;
use DeGraciaMathieu\FileExplorer\File as FileExplorer;

class FileAdapter implements File
{
    public function __construct(
        private FileExplorer $file,
    ) {}

    public function fullPath(): string
    {
        return $this->file->fullPath;
    }
}
