<?php

namespace App\Infrastructure\File\Ports;

interface File
{
    public function fullPath(): string;
}
