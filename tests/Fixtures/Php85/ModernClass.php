<?php

namespace Tests\Fixtures\Php85;

use Attribute;
use DateTimeInterface;
use IteratorAggregate;

#[Attribute]
class CustomAttribute {}

interface Contract {}

abstract class AbstractBase {}

enum Status: string {
    case Active = 'active';
}

final class ModernClass extends AbstractBase implements Contract, IteratorAggregate
{
    public function __construct(
        private readonly DateTimeInterface $clock,
    ) {}

    #[CustomAttribute]
    public function handle(Status|Contract|null $value): ?DateTimeInterface
    {
        return $this->clock;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator([]);
    }
}
