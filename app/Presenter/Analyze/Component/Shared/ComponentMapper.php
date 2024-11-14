<?php

namespace App\Presenter\Analyze\Component\Shared;

use Illuminate\Support\Str;

class ComponentMapper
{
    public function __construct(
        private readonly ComponentFactory $componentFactory,
    ) {}

    /**
     * @var array<string, array<AnalyzeMetric>> $metrics
     */
    public function from(array $metrics): array
    {
        $components = array_keys($metrics);

        $items = [];

        foreach ($metrics as $component => $componentMetrics) {

            $collector = new Collector();

            $collector->setName($component);

            foreach ($componentMetrics as $metric) {

                $collector->collect($metric);

                foreach ($metric->dependencies() as $dependency) {

                    foreach ($components as $otherComponent) {

                        if (Str::startsWith($dependency, $otherComponent)) {

                            if ($otherComponent === $component) {
                                continue;
                            }

                            if (! $collector->hasDependency($otherComponent)) {
                                $collector->addDependency($otherComponent);
                            }
                        }
                    }
                }
            }

            $items[] = $this->componentFactory->make($collector);
        }

        return $items;
    }
} 