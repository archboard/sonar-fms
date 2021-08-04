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

    public function description(): string
    {
        return __('This is a bank transfer collection method. For payments received, you will likely need to record payments manually.');
    }
}
