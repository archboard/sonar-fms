<?php

namespace App\PaymentMethods;

class BankTransfer extends Cash implements PaymentMethodDriver
{
    public function key(): string
    {
        return 'bank_transfer';
    }

    public function label(): string
    {
        return __('Bank transfer');
    }
}
