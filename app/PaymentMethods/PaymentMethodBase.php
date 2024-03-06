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

    public function includePaymentMethodInResource(): bool
    {
        return $this->includePaymentMethodInResource;
    }

    public function getInvoiceContent(): ?string
    {
        return $this->paymentMethod->options['instructions'] ?? null;
    }
}
