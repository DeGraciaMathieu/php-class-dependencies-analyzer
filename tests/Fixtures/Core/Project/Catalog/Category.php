<?php

namespace Shop\Catalog;

class Category
{
    /** @var list<Product> */
    private array $products = [];

    public function add(Product $product): void
    {
        $this->products[] = $product;
    }
}
