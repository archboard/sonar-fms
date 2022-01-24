<?php

namespace App\PaymentMethods;

use App\Traits\SetsPaymentMethods;

class Skiply extends Cash implements PaymentMethodDriver
{
    use SetsPaymentMethods;

    public function key(): string
    {
        return 'skiply';
    }

    public function label(): string
    {
        return 'Skiply';
    }

    public function description(): string
    {
        return __('This is a for a transfer with Skiply. Record the details for the transaction.');
    }

    public function getImportDetectionValues(): array
    {
        return [
            'Skiply',
        ];
    }
}
