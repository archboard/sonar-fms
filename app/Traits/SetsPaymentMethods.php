<?php

namespace App\Traits;

use App\Models\PaymentMethod;
use App\PaymentMethods\PaymentMethodDriver;

trait SetsPaymentMethods
{
    public function setPaymentMethod(?PaymentMethod $method): PaymentMethodDriver
    {
        $this->paymentMethod = $method;

        return $this;
    }

    public function setIncludePaymentMethodInResource(bool $include): PaymentMethodDriver
    {
        $this->includePaymentMethodInResource = $include;

        return $this;
    }
}
