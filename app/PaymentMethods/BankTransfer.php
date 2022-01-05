<?php

namespace App\PaymentMethods;

use App\Traits\SetsPaymentMethods;

class BankTransfer extends Cash implements PaymentMethodDriver
{
    use SetsPaymentMethods;

    public function key(): string
    {
        return 'bank_transfer';
    }

    public function label(): string
    {
        return __('Bank transfer');
    }

    public function description(): string
    {
        return __('This is a bank transfer collection method. For payments received, you will likely need to record payments manually.');
    }

    public function getImportDetectionValues(): array
    {
        return [
            'bank',
            'bank transfer',
            'ACH',
            'bank_transfer',
        ];
    }
}
