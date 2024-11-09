<?php

namespace App\Infrastructure\Analyze\Adapters\Jerowork\Collectors;

class ClassTypeCollector
{
    public ?string $namespace = null;
    public ?string $className = null;
    public ?bool $isAbstract = false;
    public ?bool $isInterface = false;

    public function __construct(
        public string $filePath,
    ) {}

    public function isInterface(): bool
    {
        return $this->isInterface;
    }

    public function isAbstract(): bool
    {
        return $this->isAbstract;
    }
}
