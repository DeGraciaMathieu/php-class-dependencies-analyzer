<?php

namespace App\Presenter\Analyze\Shared\Network;

interface Network
{
    public function nodes(): array;
    public function edges(): array;
    public function countNodes(): int;
}
