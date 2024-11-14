<?php

namespace App\Presenter\Analyze\Component\Graph;

use App\Presenter\Analyze\Shared\Network\Network;
use App\Presenter\Analyze\Shared\Network\NetworkBuilder;
use App\Presenter\Analyze\Component\Shared\ComponentMapper;
use App\Presenter\Analyze\Shared\Network\NetworkAttributesMapper;

class GraphMapper 
{
    public function __construct(
        private readonly ComponentMapper $componentMapper,
        private readonly NetworkAttributesMapper $networkAttributesMapper,
        private readonly NetworkBuilder $networkBuilder,
    ) {}

    public function from(array $metrics): Network
    {
        $components = $this->componentMapper->from($metrics);

        $networkAttributes = $this->networkAttributesMapper->map($components);

        return $this->networkBuilder->build($networkAttributes);
    }
}
