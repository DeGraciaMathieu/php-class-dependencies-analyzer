<?php

namespace App\Presenter\Commands\Analyze;

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\View;
use App\Application\Analyze\AnalyzeResponse;
use App\Application\Analyze\AnalyzePresenter;
use Illuminate\Support\Str;

class GraphPresenter implements AnalyzePresenter
{
    public function __construct(
        private OutputStyle $output,
    ) {}

    public function hello(): void
    {
        $this->output->writeln('❀ PHP Instability Analyzer ❀');
    }

    public function present(AnalyzeResponse $response): void
    {
        $nodes = [];
        $edges = [];
        $nodeNames = [];

        foreach ($response->metrics as $item) {
            $nodes[] = [
                'data' => [
                    'id' => self::slug($item['name']), 
                    'instability' => $item['instability'],
                ],
            ];
            $nodeNames[] = self::slug($item['name']);
        }

        foreach ($response->metrics as $item) {
            foreach ($item['dependencies'] as $dependency) {
                if (!in_array($dependency, $nodeNames)) {
                    $nodes[] = [
                        'data' => [
                            'id' => self::slug($dependency), 
                            'instability' => 0
                        ],
                    ];
                    $nodeNames[] = self::slug($dependency);
                }
                $edges[] = [
                    'data' => [
                        'source' => self::slug($item['name']), 
                        'target' => self::slug($dependency),
                    ],
                ];
            }
        }

        $view = View::make('graph', ['nodes' => $nodes, 'edges' => $edges]);

        $html = $view->render();

        file_put_contents('graph.html', $html);

        $this->output->writeln('In progress...');
        $this->output->writeln('Graph generated');
    }

    private static function slug(string $name): string
    {
        if ($name === '') {
            return 'unknown';
        }
        return Str::afterLast($name, '\\');
    }

    public function error(string $message): void
    {
        $this->output->error($message);
    }
}
