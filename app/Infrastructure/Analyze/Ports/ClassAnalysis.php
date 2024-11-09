<?php

namespace App\Infrastructure\Analyze\Ports;

interface ClassAnalysis
{
    public function fqcn(): string;
    public function dependencies(): array;
    public function isInterface(): bool;
    public function isAbstract(): bool;
}
