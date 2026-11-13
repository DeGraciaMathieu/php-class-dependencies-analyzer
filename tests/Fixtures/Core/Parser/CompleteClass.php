<?php

namespace Tests\Fixtures\Core\Parser;

use Fixture\Attributes\Route;
use Fixture\Contracts\Cacheable;
use Fixture\Contracts\Countable as CountableContract;
use Fixture\Domain\Address;
use Fixture\Domain\Money;
use Fixture\Domain\Order;
use Fixture\Exceptions\NotFound;
use Fixture\Support\Base;
use Fixture\Support\HasEvents;

#[Route('/complete')]
final class CompleteClass extends Base implements Cacheable, CountableContract
{
    use HasEvents;

    private ?Address $address = null;

    public function __construct(
        private Money|Order $value,
        private \Fixture\Domain\Left&\Fixture\Domain\Right $both,
    ) {}

    public function handle(self $other, int $count, mixed $anything): static
    {
        $item = new \Fixture\Domain\Item;
        $total = \Fixture\Support\Calculator::sum($count);
        $max = \Fixture\Support\Limits::MAX;
        $registry = \Fixture\Support\Registry::$instances;
        $class = \Fixture\Domain\Reference::class;

        if ($other instanceof \Fixture\Domain\Checked) {
            strlen(PHP_EOL);
            \Fixture\Support\helper();
        }

        try {
            parent::handle();
        } catch (NotFound|\Fixture\Exceptions\Timeout $exception) {
        }

        return new self;
    }

    public function result(): ?\Fixture\Domain\Result
    {
        return null;
    }

    public function self(): CompleteClass
    {
        return $this;
    }
}
