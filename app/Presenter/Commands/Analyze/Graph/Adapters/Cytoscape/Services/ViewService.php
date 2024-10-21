<?php

namespace App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Services;

use Illuminate\Support\Facades\View;
use App\Presenter\Commands\Analyze\Graph\Adapters\Cytoscape\Aggregates\Graph;

class ViewService
{
    public function render(Graph $graph): string
    {
        $view = View::make('graph', $graph->toArray());

        return $view->render();
    }
}
