<?php

namespace App\Infrastructure\Analyze\Adapters\Jerowork;

use PhpParser\Parser;
use App\Infrastructure\Analyze\Ports\ClassAnalysis;
use App\Infrastructure\Analyze\Ports\ClassDependenciesParser;
use App\Infrastructure\Analyze\Adapters\Jerowork\NodeTraverserFactory;
use Jerowork\ClassDependenciesParser\ClassDependencies as ClassDependenciesCollector;
use App\Infrastructure\Analyze\Adapters\Jerowork\DataTransferObjects\ClassAnalysisAdapter;
use App\Infrastructure\Analyze\Adapters\Jerowork\Collectors\ClassTypeCollector;

class ClassDependenciesParserAdapter implements ClassDependenciesParser
{
    public function __construct(
        private readonly Parser $parser,
        private readonly NodeTraverserFactory $traverserFactory,
    ) {}

    public function parse(string $filePath): ClassAnalysis
    {
        $fileContent = $this->getFileContent($filePath);
        
        $ast = $this->parseContent($fileContent);
        
        $collectors = $this->makeCollectors($filePath);

        $this->traverse($ast, $collectors);

        return ClassAnalysisAdapter::fromArray($collectors);
    }

    private function getFileContent(string $filePath): ?string
    {
        return file_get_contents($filePath);
    }

    private function parseContent(string $content): ?array
    {
        return $this->parser->parse($content);
    }

    private function makeCollectors(string $filePath): array
    {
        return [
            'dependencies' => new ClassDependenciesCollector($filePath),
            'type' => new ClassTypeCollector($filePath),
        ];
    }

    private function traverse(array $ast, array &$collectors): void
    {
        $traverser = $this->traverserFactory->createTraverser($collectors);

        $traverser->traverse($ast);
    }
}
