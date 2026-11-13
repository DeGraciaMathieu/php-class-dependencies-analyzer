<?php

namespace Shop\Billing;

use Shop\Ordering\Order;
use Vendor\Money\Money;

class Invoice
{
    use HasTaxes;

    public function __construct(
        public readonly Order $order,
        public readonly Money $total,
    ) {}
}
