<?php

namespace Tests\Fixtures\Core\Parser;

enum Status: string implements \Fixture\Contracts\HasLabel
{
    case Active = 'active';

    public function label(): \Fixture\Support\Label
    {
        return new \Fixture\Support\Label($this->value);
    }
}
