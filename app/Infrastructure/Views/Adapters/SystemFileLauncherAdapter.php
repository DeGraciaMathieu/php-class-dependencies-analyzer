<?php

namespace App\Infrastructure\Views\Adapters;

use App\Presenter\Analyze\Shared\Views\SystemFileLauncher;

class SystemFileLauncherAdapter implements SystemFileLauncher
{
    private string $file = 'graph.html';

    public function open(): void
    {
        $command = $this->getExecCommand();

        exec("$command $this->file");
    }

    public function save(string $html): void
    {
        file_put_contents('graph.html', $html);
    }

    private function getExecCommand(): string
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => 'start',
            'Darwin' => 'open',
            default => 'xdg-open',
        };
    }
}
