<?php

declare(strict_types=1);

namespace App\Core\Parser\PhpParser;

use App\Core\Parser\ClassParser;
use App\Core\Parser\ParseError;
use App\Core\Parser\ParseResult;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final readonly class PhpParserClassParser implements ClassParser
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    public function parse(string $file): ParseResult
    {
        $code = @file_get_contents($file);

        if ($code === false) {
            return new ParseResult(errors: [new ParseError($file, 'Unable to read file.')]);
        }

        try {
            $statements = $this->parser->parse($code) ?? [];
        } catch (Error $error) {
            return new ParseResult(errors: [new ParseError($file, $error->getMessage())]);
        }

        $collector = new DependencyCollector($file);

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);
        $traverser->addVisitor($collector);
        $traverser->traverse($statements);

        return new ParseResult(classes: $collector->classes());
    }
}
