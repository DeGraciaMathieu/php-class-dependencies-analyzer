<?php

declare(strict_types=1);

namespace App\Core\Parser\PhpParser;

use App\Core\Parser\ClassKind;
use App\Core\Parser\ParsedClass;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Collects the names used in class position by each named classlike.
 * Expects names to be resolved beforehand by NameResolver.
 */
final class DependencyCollector extends NodeVisitorAbstract
{
    private const NON_CLASS_NAMES = [
        'self', 'static', 'parent',
        'bool', 'int', 'float', 'string', 'array', 'object', 'iterable', 'callable',
        'mixed', 'never', 'void', 'null', 'true', 'false',
    ];

    /** @var list<ParsedClass> */
    private array $classes = [];

    /** @var list<array{node: Stmt\ClassLike, dependencies: array<string, true>}> */
    private array $stack = [];

    public function __construct(
        private readonly string $file,
    ) {}

    /**
     * @return list<ParsedClass>
     */
    public function classes(): array
    {
        return $this->classes;
    }

    public function enterNode(Node $node): null
    {
        if ($node instanceof Stmt\ClassLike && $node->name !== null) {
            $this->stack[] = ['node' => $node, 'dependencies' => []];
        }

        foreach ($this->namesInClassPosition($node) as $name) {
            $this->addDependency($name);
        }

        return null;
    }

    public function leaveNode(Node $node): null
    {
        if ($node instanceof Stmt\ClassLike && $node->name !== null) {
            $this->classes[] = $this->toParsedClass(array_pop($this->stack));
        }

        return null;
    }

    /**
     * @return list<Node|null>
     */
    private function namesInClassPosition(Node $node): array
    {
        return match (true) {
            $node instanceof Stmt\Class_ => [$node->extends, ...$node->implements],
            $node instanceof Stmt\Interface_ => $node->extends,
            $node instanceof Stmt\Enum_ => $node->implements,
            $node instanceof Stmt\TraitUse => $node->traits,
            $node instanceof Node\FunctionLike => [$node->getReturnType()],
            $node instanceof Node\Param,
            $node instanceof Stmt\Property,
            $node instanceof Stmt\ClassConst => [$node->type],
            $node instanceof Expr\New_,
            $node instanceof Expr\StaticCall,
            $node instanceof Expr\StaticPropertyFetch,
            $node instanceof Expr\ClassConstFetch,
            $node instanceof Expr\Instanceof_ => [$node->class],
            $node instanceof Stmt\Catch_ => $node->types,
            $node instanceof Node\Attribute => [$node->name],
            default => [],
        };
    }

    private function addDependency(?Node $node): void
    {
        if ($this->stack === []) {
            return;
        }

        foreach ($this->classNamesIn($node) as $name) {
            $this->stack[array_key_last($this->stack)]['dependencies'][$name] = true;
        }
    }

    /**
     * Unwraps nullable, union and intersection types; ignores identifiers and expressions.
     *
     * @return list<string>
     */
    private function classNamesIn(?Node $node): array
    {
        if ($node instanceof Node\NullableType) {
            return $this->classNamesIn($node->type);
        }

        if ($node instanceof Node\UnionType || $node instanceof Node\IntersectionType) {
            return array_merge(...array_map($this->classNamesIn(...), $node->types));
        }

        if (! $node instanceof Node\Name) {
            return [];
        }

        $name = $node->toString();

        return in_array(strtolower($name), self::NON_CLASS_NAMES, true) ? [] : [$name];
    }

    /**
     * @param  array{node: Stmt\ClassLike, dependencies: array<string, true>}  $context
     */
    private function toParsedClass(array $context): ParsedClass
    {
        $classLike = $context['node'];
        $fqcn = $classLike->namespacedName->toString();
        $dependencies = array_keys($context['dependencies']);

        return new ParsedClass(
            fqcn: $fqcn,
            kind: $this->kindOf($classLike),
            dependencies: array_values(array_diff($dependencies, [$fqcn])),
            file: $this->file,
        );
    }

    private function kindOf(Stmt\ClassLike $classLike): ClassKind
    {
        return match (true) {
            $classLike instanceof Stmt\Interface_ => ClassKind::Interface_,
            $classLike instanceof Stmt\Trait_ => ClassKind::Trait_,
            $classLike instanceof Stmt\Enum_ => ClassKind::Enum_,
            $classLike instanceof Stmt\Class_ && $classLike->isAbstract() => ClassKind::AbstractClass,
            default => ClassKind::Class_,
        };
    }
}
