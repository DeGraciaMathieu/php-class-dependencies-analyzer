<?php

namespace App\Application\Weakness;

use App\Domain\Aggregators\DependencyAggregator;

class WeaknessResponseMapper
{
    public function from(DependencyAggregator $dependencyAggregator): WeaknessResponse
    {
        $classes = $dependencyAggregator->toArray();

        return new WeaknessResponse(
            count: $dependencyAggregator->count(),
            metrics: $this->formatClassesMetrics($classes),
        );
    }

    private function formatClassesMetrics(array $classes): array
    {
        $metrics = [];

        foreach ($classes as $class) {

            $instability = $class['instability'];

            foreach ($class['dependencies'] as $dependency) {

                $dependency = $classes[$dependency] ?? null;

                if ($dependency && $dependency['instability'] > $instability) {

                    $metrics[] = [
                        'class' => $class['name'],
                        'class_instability' => $instability,
                        'dependency' => $dependency['name'],
                        'dependency_instability' => $dependency['instability'],
                        'score' => $dependency['instability'] - $instability,
                    ];
                }
            }
        }

        return $metrics;
    }
}
