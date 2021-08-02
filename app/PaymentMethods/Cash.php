<?php

namespace App\PaymentMethods;

class Cash extends PaymentMethodBase implements PaymentMethodDriver
{
    public function label(): string
    {
        return __('Cash/check');
    }

    public function description(): string
    {
        return __('This is an offline collection method. For payments received, you will need to record payments manually.');
    }

    public function component(): ?string
    {
        return null;
    }
}
