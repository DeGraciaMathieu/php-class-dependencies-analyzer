<?php

declare(strict_types=1);

namespace App\Core\Parser;

enum ClassKind
{
    case Class_;
    case AbstractClass;
    case Interface_;
    case Trait_;
    case Enum_;

    public function isAbstract(): bool
    {
        return $this === self::AbstractClass || $this === self::Interface_;
    }
}
