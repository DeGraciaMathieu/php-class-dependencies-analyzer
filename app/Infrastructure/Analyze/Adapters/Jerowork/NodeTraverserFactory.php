<?php

namespace App\Infrastructure\Analyze\Adapters\Jerowork;

use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use App\Infrastructure\Analyze\Adapters\Jerowork\Visitors\DetectClassTypeVisitor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\ParseClassFqnNodeVisitor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\ParseImportedFqnNodeVisitor;

class NodeTraverserFactory
{
    public function createTraverser(array &$collectors): NodeTraverserInterface
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor(new ParseClassFqnNodeVisitor($collectors['dependencies']));
        $traverser->addVisitor(new ParseImportedFqnNodeVisitor($collectors['dependencies']));
        $traverser->addVisitor(new DetectClassTypeVisitor($collectors['type']));

        return $traverser;
    }
}
