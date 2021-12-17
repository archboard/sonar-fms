<?php

namespace Tests\Traits;

use App\Models\Invoice;
use App\Models\PaymentImport;

trait CreatesPaymentImports
{
    protected function createImport(string $file = 'small_payments.xlsx', array $attributes = []): PaymentImport
    {
        $originalPath = (new PaymentImport)
            ->storeFile($this->getUploadedFile($file), $this->school);
        $defaults = [
            'tenant_id' => $this->tenant->id,
            'user_uuid' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => $originalPath,
        ];

        return PaymentImport::create(array_merge($defaults, $attributes));
    }

    protected function addPaymentInvoices(PaymentImport $import, string $invoiceColumn, bool $allowCombined = false)
    {
        foreach ($import->getImportContents() as $row) {
            if ($invoiceNumber = $row->get($invoiceColumn)) {
                $function = $this->faker->boolean() && $allowCombined
                    ? 'createCombinedInvoice'
                    : 'createInvoice';

                /** @var Invoice $invoice */
                $invoice = $this->$function();

                foreach ($invoice->children as $child) {
                    $child->invoiceScholarships()->delete();
                    $child->unsetRelations();
                    $child->setCalculatedAttributes(true);
                }

                // No scholarship funny business
                $invoice->invoiceScholarships()->delete();
                $invoice->unsetRelations();

                $invoice->fill(['invoice_number' => strtoupper($invoiceNumber)])
                    ->setCalculatedAttributes()
                    ->save();
            }
        }
    }
}
