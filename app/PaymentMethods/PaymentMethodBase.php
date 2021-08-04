<?php

namespace App\PaymentMethods;

use App\Models\PaymentMethod;

abstract class PaymentMethodBase
{
    protected ?PaymentMethod $paymentMethod;
    protected bool $includePaymentMethodInResource = false;

    public function __construct(?PaymentMethod $paymentMethod = null)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

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

    public function includePaymentMethodInResource(): bool
    {
        return $this->includePaymentMethodInResource;
    }
}
