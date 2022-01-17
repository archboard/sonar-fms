<?php

namespace Tests\Traits;

use App\Models\Invoice;
use App\Models\InvoicePayment;

trait CreatesPayments
{
    use CreatesInvoice;

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createPayment(array $attributes = [], ?Invoice $invoice = null): InvoicePayment
    {
        $invoice = $invoice ?: $this->createInvoice();
        $defaultAttributes = [
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'amount' => rand(1, $invoice->remaining_balance),
            'recorded_by' => $this->user->uuid,
            'made_by' => $this->createUser()->uuid,
        ];

        return $invoice->invoicePayments()->create(
            array_merge($defaultAttributes, $attributes)
        );
    }
}
