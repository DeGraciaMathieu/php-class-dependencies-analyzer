<?php

namespace Shop\Ordering;

interface OrderRepository
{
    public function save(Order $order): void;
}
