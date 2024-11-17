<?php

namespace App\Infrastructure\Analyze\Adapters\Jerowork;

use PhpParser\Node;
use PhpParser\Node\Name;
use Jerowork\ClassDependenciesParser\ClassDependencies;
use Jerowork\ClassDependenciesParser\PhpParser\NodeVisitor\InlineFqnParser\Decliner\InlineFqnDecliner;

final class NativeDecliner implements InlineFqnDecliner
{
    private array $primitiveTypes = [
        'string',
        'int',
        'float',
        'bool',
        'array',
        'object',
        'null',
        'void',
        'mixed',
        'never',
        'callable',
        'iterable',
        'false',
        'true',
        'self',
        'parent',
        'static',
        'PHP_EOL',
    ];

    public function shouldDecline(Node $parent, Name $name, ClassDependencies $classDependencies): bool
    {
        $fqn = $name->toString();

        return $this->isNativePrimitiveType($fqn) || $this->isNativePhpClass($fqn);
    }

    private function isNativePrimitiveType(string $fqn): bool
    {
        return in_array(strtolower($fqn), $this->primitiveTypes, true);
    }

    private function isNativePhpClass(string $fqn): bool
    {
        return function_exists($fqn) ||
            class_exists($fqn, false) ||
            interface_exists($fqn, false);
    }
}
