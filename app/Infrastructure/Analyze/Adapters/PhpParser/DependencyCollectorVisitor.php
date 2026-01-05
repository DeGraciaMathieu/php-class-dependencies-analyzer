<?php

namespace App\Infrastructure\Analyze\Adapters\PhpParser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Enum_;

final class DependencyCollectorVisitor extends NodeVisitorAbstract
{
    private array $dependencies = [];
    private ?string $fqcn = null;
    private bool $isInterface = false;
    private bool $isAbstract = false;

    public function enterNode(Node $node): void
    {
        if ($node instanceof Class_) {
            $this->fqcn = $node->namespacedName?->toString();
            $this->isAbstract = $node->isAbstract();
            $this->isInterface = false;
        }

        if ($node instanceof Interface_ && $this->fqcn === null) {
            $this->fqcn = $node->namespacedName?->toString();
            $this->isInterface = true;
        }

        if ($node instanceof Enum_) {
            $this->fqcn = $node->namespacedName?->toString();
        }

        if ($node instanceof Node\Name) {
            $name = $node->toString();
            if (! $this->isBuiltinType($name)) {
                $this->dependencies[] = $name;
            }
        }

        if ($node instanceof Node\Attribute) {
            $this->dependencies[] = $node->name->toString();
        }
    }

    public function analysis(): AstClassAnalysis
    {
        return new AstClassAnalysis(
            fqcn: $this->fqcn ?? '',
            dependencies: array_values(array_unique($this->dependencies)),
            isInterface: $this->isInterface,
            isAbstract: $this->isAbstract,
        );
    }

    private function isBuiltinType(string $name): bool
    {
        return in_array(strtolower($name), [
            'string', 'int', 'float', 'bool', 'array', 'callable',
            'iterable', 'object', 'mixed', 'null', 'false', 'true',
            'never', 'void', 'self', 'parent', 'static',
        ], true);
    }
}
