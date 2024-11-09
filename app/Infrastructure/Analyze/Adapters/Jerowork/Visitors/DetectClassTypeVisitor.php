<?php

namespace App\Infrastructure\Analyze\Adapters\Jerowork\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use App\Infrastructure\Analyze\Adapters\Jerowork\Collectors\ClassTypeCollector;

class DetectClassTypeVisitor extends NodeVisitorAbstract
{
    public ?string $namespace = null;
    public ?string $className = null;
    public ?bool $isAbstract = false;
    public ?bool $isInterface = false;

    public function __construct(
        private ClassTypeCollector $collector,
    ) {}

    public function beforeTraverse(array $nodes)
    {
        //
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->collector->namespace = (string) $node->name;
        }

        if ($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Interface_ || $node instanceof Enum_) {
            
            $this->collector->className = (string) $node->name;

            if ($node instanceof Class_) {
                $this->collector->isAbstract = $node->isAbstract();
            }

            $this->collector->isInterface = $node instanceof Interface_;
        }
    }

    public function leaveNode(Node $node)
    {
        //
    }
}
