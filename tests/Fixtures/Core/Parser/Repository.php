<?php

namespace Tests\Fixtures\Core\Parser;

interface Repository extends \Fixture\Contracts\Readable, \Fixture\Contracts\Writable
{
    public function find(int $id): ?\Fixture\Domain\Order;
}
