<?php

namespace App\PaymentMethods;

use App\Models\PaymentMethod;

interface PaymentMethodDriver
{
    public function __construct(?PaymentMethod $method);

    public function key(): string;

    public function label(): string;

    public function description(): string;

    public function component(): ?string;

    public function getPaymentMethod(): ?PaymentMethod;

    public function setPaymentMethod(?PaymentMethod $method): PaymentMethodDriver;

    public function getValidationRules(): array;

    public function includePaymentMethodInResource(): bool;

    public function setIncludePaymentMethodInResource(bool $include): PaymentMethodDriver;

    public function getInvoiceContent(): ?string;

    public function getImportDetectionValues(): array;
}
