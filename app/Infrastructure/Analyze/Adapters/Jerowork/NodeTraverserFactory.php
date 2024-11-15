<?php

namespace App\Infrastructure\Analyze\Adapters\Jerowork;

use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use App\Infrastructure\Analyze\Adapters\Jerowork\NativeDecliner;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\ParseClassFqnNodeVisitor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\ParseInlineFqnNodeVisitor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\ParseImportedFqnNodeVisitor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\InlineFqnParser\Decliner\NamespaceDecliner;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\InlineFqnParser\Decliner\ImportedFqnDecliner;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\InlineFqnParser\Decliner\PhpNativeAccessorDecliner;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\InlineFqnParser\Processor\RootLevelFunctionProcessor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\InlineFqnParser\Processor\FullyQualifiedNameProcessor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\InlineFqnParser\Processor\InlineFqnIsImportedProcessor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\InlineFqnParser\Processor\InlineFqnIsImportedAsAliasProcessor;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\InlineFqnParser\Processor\InlineFqnWithinSameNamespaceProcessor;

class NodeTraverserFactory
{
    public function createTraverser(array &$collectors): NodeTraverserInterface
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor(new ParseClassFqnNodeVisitor($collectors['dependencies']));
        $traverser->addVisitor(new ParseImportedFqnNodeVisitor($collectors['dependencies']));
        $traverser->addVisitor(new ParseInlineFqnNodeVisitor(
            $collectors['dependencies'],
            [
                new NamespaceDecliner(),
                new ImportedFqnDecliner(),
                new PhpNativeAccessorDecliner(),
                new NativeDecliner(),
            ],
            [
                new FullyQualifiedNameProcessor(),
                new RootLevelFunctionProcessor(),
                new InlineFqnIsImportedProcessor(),
                new InlineFqnIsImportedAsAliasProcessor(),
                new InlineFqnWithinSameNamespaceProcessor(),
            ],
        ));


        return $traverser;
    }
}
