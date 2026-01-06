<?php

declare(strict_types=1);

namespace App\Presenter\Analyze\Class\Bubble;

use Throwable;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;

class BubblePresenter implements AnalyzePresenter
{
    public function __construct(
        private readonly BubbleMapper $mapper,
    ) {}

    public function present(AnalyzeResponse $response): void
    {
        $metrics = $response->metrics;

        $foldersData = $this->mapper->from($metrics);

        echo json_encode($foldersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function hello(): void
    {
    }

    public function error(Throwable $e): void
    {
    }
}