<?php

namespace App\PaymentMethods;

use App\Models\PaymentMethod;

class PaymentMethodBase
{
    public PaymentMethod $paymentMethod;

    public function __construct(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }
}
