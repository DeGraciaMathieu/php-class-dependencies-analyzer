<?php

namespace App\Presenter\Analyze\Shared\Filters\Contracts;

use App\Application\Analyze\AnalyzeMetric;

/**
 * Transformers manipulate metrics by reshaping the data structure before mapping.
 */
interface Transformer
{
    /**
     * @param array<AnalyzeMetric> $metrics
     * @return array<AnalyzeMetric>
     */
    public function apply(array $metrics): array;
}
