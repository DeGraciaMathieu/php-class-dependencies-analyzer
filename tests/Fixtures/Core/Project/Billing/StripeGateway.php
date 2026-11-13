<?php

namespace Shop\Billing;

use Vendor\Stripe\StripeClient;

class StripeGateway implements PaymentGateway
{
    public function __construct(
        private readonly StripeClient $client,
    ) {}

    public function charge(Invoice $invoice): void
    {
        $this->client->charge($invoice->total);
    }
}
