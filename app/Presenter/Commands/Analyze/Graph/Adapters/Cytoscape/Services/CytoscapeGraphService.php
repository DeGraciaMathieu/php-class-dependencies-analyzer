<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape;

use App\Presenter\Commands\Analyze\Graph\Ports\GraphService;

class CytoscapeGraphService implements GraphService
{
    public function __construct(
        private GraphFactory $graphFactory,
        private ViewService $viewService,
    ) {}

    public function generate(array $metrics): string
    {
        $graphAggregate = $this->graphFactory->make($metrics);

        return $this->viewService->render($graphAggregate);
    }
}
