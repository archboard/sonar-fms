<?php

namespace App\PaymentMethods;

use App\Traits\SetsPaymentMethods;

class Cash extends PaymentMethodBase implements PaymentMethodDriver
{
    use SetsPaymentMethods;

    public function key(): string
    {
        return 'cash';
    }

    public function label(): string
    {
        return __('Cash/check');
    }

    public function description(): string
    {
        return __('This is an offline collection method for cash or check. For payments received, you will need to record payments manually.');
    }

    public function component(): ?string
    {
        return 'Cash';
    }

    public function getValidationRules(): array
    {
        return [
            'instructions' => 'nullable',
        ];
    }

    public function getImportDetectionValues(): array
    {
        return [
            'cash',
        ];
    }
}
