<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape;

use Illuminate\Support\Facades\View;
use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Aggregates\GraphAggregate;

class ViewService
{
    public function render(GraphAggregate $graphAggregate): string
    {
        $view = View::make('graph', $graphAggregate->toArray());

        return $view->render();
    }
}
