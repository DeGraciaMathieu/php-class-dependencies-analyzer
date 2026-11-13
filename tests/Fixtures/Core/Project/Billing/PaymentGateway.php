<?php

namespace Shop\Billing;

interface PaymentGateway
{
    public function charge(Invoice $invoice): void;
}
