<?php

declare(strict_types=1);

namespace App\Core\Parser;

interface ClassParser
{
    public function parse(string $file): ParseResult;
}
