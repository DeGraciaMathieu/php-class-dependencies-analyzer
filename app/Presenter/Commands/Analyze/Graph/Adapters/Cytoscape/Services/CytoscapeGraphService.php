<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Services;

use App\Presenter\Commands\Analyze\Graph\Ports\GraphService;
use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Services\ViewService;
use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Services\GraphFactory;

class CytoscapeGraphService implements GraphService
{
    public function __construct(
        private GraphFactory $graphFactory,
        private ViewService $viewService,
    ) {}

    public function generate(array $metrics): string
    {
        $graph = $this->graphFactory->make($metrics);

        return $this->viewService->render($graph);
    }
}
