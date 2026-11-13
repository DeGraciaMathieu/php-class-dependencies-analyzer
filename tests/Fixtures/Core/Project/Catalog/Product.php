<?php

namespace Shop\Catalog;

use DateTimeImmutable;
use Vendor\Money\Money;

class Product
{
    public function __construct(
        public readonly string $sku,
        public readonly Money $price,
        public readonly Category $category,
        public readonly DateTimeImmutable $createdAt,
    ) {}
}
