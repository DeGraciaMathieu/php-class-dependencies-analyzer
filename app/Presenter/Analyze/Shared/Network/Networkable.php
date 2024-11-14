<?php

namespace App\Presenter\Analyze\Shared\Network;

interface Networkable
{
    public function name(): string;
    public function instability(): float;
    public function dependencies(): array;
}
