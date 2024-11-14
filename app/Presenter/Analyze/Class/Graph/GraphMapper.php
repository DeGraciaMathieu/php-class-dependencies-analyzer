<?php

namespace App\Presenter\Analyze\Class\Graph;

use App\Application\Analyze\AnalyzeMetric;
use App\Presenter\Analyze\Shared\Network\Network;
use App\Presenter\Analyze\Class\Shared\MetricMapper;
use App\Presenter\Analyze\Shared\Network\NetworkBuilder;
use App\Presenter\Analyze\Shared\Network\NetworkAttributesMapper;

class GraphMapper
{
    public function __construct(
        private readonly MetricMapper $metricMapper,
        private readonly NetworkAttributesMapper $networkAttributesMapper,
        private readonly NetworkBuilder $networkBuilder,
    ) {}

    /**
     * @param array<AnalyzeMetric> $metrics
     */
    public function from(array $metrics): Network
    {
        $metrics = $this->metricMapper->from($metrics);

        $networkAttributes = $this->networkAttributesMapper->map($metrics);

        $network = $this->networkBuilder->build($networkAttributes);

        return $network;
    }
}
