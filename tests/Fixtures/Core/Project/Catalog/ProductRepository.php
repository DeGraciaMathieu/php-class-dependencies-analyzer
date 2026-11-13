<?php

namespace Shop\Catalog;

interface ProductRepository
{
    public function find(string $sku): ?Product;
}
