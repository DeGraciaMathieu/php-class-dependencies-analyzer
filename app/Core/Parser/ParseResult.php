<?php

declare(strict_types=1);

namespace App\Core\Parser;

final readonly class ParseResult
{
    /**
     * @param  list<ParsedClass>  $classes
     * @param  list<ParseError>  $errors
     */
    public function __construct(
        public array $classes = [],
        public array $errors = [],
    ) {}
}
