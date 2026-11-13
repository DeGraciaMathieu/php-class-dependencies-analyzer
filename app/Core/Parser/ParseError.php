<?php

declare(strict_types=1);

namespace App\Core\Parser;

final readonly class ParseError
{
    public function __construct(
        public string $file,
        public string $message,
    ) {}
}
