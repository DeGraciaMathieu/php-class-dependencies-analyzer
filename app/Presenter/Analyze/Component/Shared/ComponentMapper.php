<?php

namespace App\Presenter\Analyze\Component\Shared;

use Illuminate\Support\Str;

class ComponentMapper
{
    /**
     * @todo : its work but it's not efficient
     */
    public function from(array $metrics): array
    {
        $components = array_keys($metrics);

        $tmp = [];

        foreach ($metrics as $component => $componentMetrics) {

            $instability = 0;
            $abstract = 0;

            $innerDependencies = [];

            foreach ($componentMetrics as $metric) {

                $instability += $metric->instability();
                $abstract += ($metric->abstract()) ? 1 : 0;

                foreach ($metric->dependencies() as $dependency) {

                    foreach ($components as $otherComponent) {

                        if (Str::startsWith($dependency, $otherComponent)) {

                            if ($otherComponent === $component) {
                                continue;
                            }

                            if (! in_array($otherComponent, $innerDependencies)) {
                                $innerDependencies[] = $otherComponent;
                            }
                        }
                    }
                }
            }

            $tmp[] = new Component(
                $component,
                count($componentMetrics),
                $abstract,
                $abstract / count($componentMetrics),
                $instability / count($componentMetrics),
                $innerDependencies,
            );
        }

        return $tmp;
    }
} 