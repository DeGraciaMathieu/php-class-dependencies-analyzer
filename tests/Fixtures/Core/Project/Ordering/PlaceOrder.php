<?php

namespace Shop\Ordering;

use DateTimeImmutable;
use Shop\Billing\Invoice;
use Shop\Billing\PaymentGateway;
use Shop\Catalog\ProductRepository;

class PlaceOrder
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly OrderRepository $orders,
        private readonly PaymentGateway $gateway,
    ) {}

    public function handle(string $sku): Order
    {
        $product = $this->products->find($sku);
        $order = new Order($product, new DateTimeImmutable);

        $this->orders->save($order);
        $this->gateway->charge(new Invoice($order, $product->price));

        return $order;
    }
}
