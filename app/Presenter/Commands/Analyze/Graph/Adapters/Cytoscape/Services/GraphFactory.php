<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape;

use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Aggregates\GraphAggregate;

class GraphFactory
{
    public function __construct(
        private GraphAggregate $graphAggregate,
    ) {}

    public function make(array $metrics): GraphAggregate
    {
        $this->mapNodes($metrics);
        $this->mapEdges($metrics);

        return $this->graphAggregate;
    }

    private function mapNodes(array $metrics): void
    {
        foreach ($metrics as $item) {
            $this->graphAggregate->addNode($item['name'], $item['instability']);
        }
    }

    private function mapEdges(array $metrics): void
    {
        foreach ($metrics as $item) {

            foreach ($item['dependencies'] as $dependency) {

                if ($this->graphAggregate->miss($dependency)) {
                    $this->graphAggregate->addNode($dependency);
                }

                $this->graphAggregate->addEdge(source: $item['name'], target: $dependency);
            }
        }
    }
}
