<?php

namespace App\Presenter\Analyze\Shared\Filters\Transformers;

use Illuminate\Support\Str;
use App\Presenter\Analyze\Shared\Filters\Contracts\Transformer;

class ComponentTransformer implements Transformer
{
    public function __construct(
        private readonly array $targetedComponents,
    ) {}

    public function apply(array $metrics): array
    {
        $components = [];

        foreach ($metrics as $metric) {

            foreach ($this->targetedComponents as $component) {

                $name = $metric->name();

                if ($this->isTargetedComponent($name, $component)) {

                    $components[$component][] = $metric;

                    break;
                }
            }
        }

        return $components;
    }

    private function isTargetedComponent(string $name, string $component): bool
    {
        return Str::startsWith($name, $component);
    }
}
