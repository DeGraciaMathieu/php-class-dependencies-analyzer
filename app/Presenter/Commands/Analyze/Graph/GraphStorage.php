<?php

namespace App\Presenter\Commands\Analyze\Graph;

class GraphStorage
{
    public function save(string $html): void
    {
        file_put_contents('graph.html', $html);
    }
}
