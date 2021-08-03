<?php

namespace App\PaymentMethods;

use App\Models\PaymentMethod;

abstract class PaymentMethodBase
{
    protected ?PaymentMethod $paymentMethod;

    public function __construct(?PaymentMethod $paymentMethod = null)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $method): PaymentMethodDriver
    {
        $this->paymentMethod = $method;

        return $this;
    }
}
