<?php

namespace App\PaymentMethods;

use App\Models\PaymentMethod;

interface PaymentMethodDriver
{
    public function __construct(PaymentMethod $method);
    public function label(): string;
    public function description(): string;
    public function component(): ?string;
}
