<?php

namespace App\Infrastructure\Graph\Adapters\Cytoscape;

use App\Presenter\Analyze\Shared\Network\Network;
use App\Presenter\Analyze\Shared\Network\NetworkBuilder;
use App\Infrastructure\Graph\Adapters\Cytoscape\CytoscapeNetwork;
use App\Presenter\Analyze\Shared\Network\NetworkAttribute;

class CytoscapeNetworkBuilder implements NetworkBuilder
{
    public function __construct(
        private readonly CytoscapeNetwork $network,
    ) {}
        
    public function build(array $metrics): Network
    {
        $this->mapNodes($metrics);
        $this->mapEdges($metrics);

        return $this->network;
    }

    private function mapNodes(array $metrics): void
    {
        foreach ($metrics as $item) {
            $this->network->addNode($item->name(), $item->instability());
        }
    }

    private function mapEdges(array $metrics): void
    {
        foreach ($metrics as $item) {

            foreach ($item->dependencies() as $dependency) {

                if ($this->isSelfDependency($dependency, $item)) {
                    continue;
                }

                if ($this->network->missingNode($dependency)) {
                    $this->network->addNode($dependency);
                }

                $this->network->addEdge(source: $item->name(), target: $dependency);
            }
        }
    }

    /**
     * We remove self dependency from graph for readability reasons
     */
    private function isSelfDependency(string $dependency, NetworkAttribute $item): bool
    {
        return $dependency === $item->name();
    }
}
