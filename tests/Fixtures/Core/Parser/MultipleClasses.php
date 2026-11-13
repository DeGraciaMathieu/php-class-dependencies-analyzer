<?php

namespace Tests\Fixtures\Core\Parser;

function helper(): void
{
    new \Fixture\Outside\Ignored;
}

class First
{
    public function make(): object
    {
        return new class extends \Fixture\Domain\AnonymousParent {};
    }
}

class Second extends First {}
