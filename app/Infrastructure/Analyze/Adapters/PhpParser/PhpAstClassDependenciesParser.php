<?php

namespace App\Infrastructure\Analyze\Adapters\PhpParser;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use App\Infrastructure\Analyze\Ports\ClassDependenciesParser;
use App\Infrastructure\Analyze\Ports\ClassAnalysis;

final class PhpAstClassDependenciesParser implements ClassDependenciesParser
{
    public function parse(string $file): ClassAnalysis
    {
        $code = file_get_contents($file);

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($code);

        $collector = new DependencyCollectorVisitor();

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor($collector);
        $traverser->traverse($ast);

        return $collector->analysis();
    }
}
