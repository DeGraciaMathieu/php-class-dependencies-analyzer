<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use Jerowork\ClassDependenciesParser\ClassDependencies;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\ParseImportedFqnNodeVisitor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\ParseClassFqnNodeVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor\ParentConnectingVisitor;

final class NodeTraverserFactory
{
    public function createTraverser(ClassDependencies $classDependencies): NodeTraverserInterface
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor(new ParseClassFqnNodeVisitor($classDependencies));
        $traverser->addVisitor(new ParseImportedFqnNodeVisitor($classDependencies));

        return $traverser;
    }
}
