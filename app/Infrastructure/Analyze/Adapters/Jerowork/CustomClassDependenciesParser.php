<?php

declare(strict_types=1);

namespace App\Infrastructure\Analyze\Adapters\Jerowork;

use PhpParser\Parser;
use Jerowork\ClassDependenciesParser\ClassDependencies;
use Jerowork\ClassDependenciesParser\ClassDependenciesParser;
use App\Infrastructure\Analyze\Adapters\Jerowork\NodeTraverserFactory;

class CustomClassDependenciesParser implements ClassDependenciesParser
{
    public function __construct(
        private readonly Parser $parser,
        private readonly NodeTraverserFactory $traverserFactory,
    ) {}

    public function parse(string $filePath): ClassDependencies
    {
        $classDependencies = new ClassDependencies($filePath);

        $fileAst = $this->getFileAst($filePath);

        if ($fileAst) {
            $this->traverse($fileAst, $classDependencies);
        }

        return $classDependencies;
    }

    private function getFileAst(string $filePath): ?array
    {
        return $this->parser->parse((string) file_get_contents($filePath));
    }

    private function traverse(array $fileAst, ClassDependencies $classDependencies): void
    {
        $traverser = $this->traverserFactory->createTraverser($classDependencies);

        $traverser->traverse($fileAst);
    }
}
