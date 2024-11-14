<?php

namespace App\Presenter\Analyze\Shared\Views;

interface SystemFileLauncher
{
    public function open(): void;
    public function save(string $html): void;
}
