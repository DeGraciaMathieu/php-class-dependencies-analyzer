<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape;

use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Aggregates\Graph;

class GraphFactory
{
    public function __construct(
        private Graph $graph,
    ) {}

    public function make(array $metrics): Graph
    {
        $this->mapNodes($metrics);
        $this->mapEdges($metrics);

        return $this->graph;
    }

    private function mapNodes(array $metrics): void
    {
        foreach ($metrics as $item) {
            $this->graph->addNode($item['name'], $item['instability']);
        }
    }

    private function mapEdges(array $metrics): void
    {
        foreach ($metrics as $item) {

            foreach ($item['dependencies'] as $dependency) {

                if ($this->graph->missingNode($dependency)) {
                    $this->graph->addNode($dependency);
                }

                $this->graph->addEdge(source: $item['name'], target: $dependency);
            }
        }
    }
}
