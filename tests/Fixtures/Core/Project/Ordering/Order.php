<?php

namespace Shop\Ordering;

use DateTimeImmutable;
use Shop\Catalog\Product;

class Order
{
    public OrderStatus $status = OrderStatus::Pending;

    public function __construct(
        public readonly Product $product,
        public readonly DateTimeImmutable $placedAt,
    ) {}
}
