<?php

declare(strict_types=1);

namespace App\Core\Analysis;

enum CycleLevel: string
{
    case Class_ = 'class';
    case Component = 'component';
}
