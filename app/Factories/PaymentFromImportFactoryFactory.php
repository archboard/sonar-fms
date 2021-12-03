<?php

namespace App\Factories;

use App\Jobs\SetInvoiceRemainingBalance;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PaymentImport;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\User;
use App\Traits\ConvertsExcelValues;
use App\Traits\GetsImportMappingValues;
use Illuminate\Bus\Batch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class PaymentFromImportFactoryFactory extends BaseImportFactory
{
    use ConvertsExcelValues;
    use GetsImportMappingValues;

    protected Collection $invoices;
    protected Collection $invoicePayments;
    protected bool $asModels = false;
    protected string $now;
    protected string $invoiceColumn;
    protected School $school;
    protected Collection $paymentMethods;
    protected Collection $users;

    public function __construct(protected PaymentImport $import, protected User $user)
    {
        $this->school = $this->import->school;
        $this->invoices = collect();
        $this->invoicePayments = collect();
        $this->results = collect();
        $this->contents = $this->import->getImportContents();
        $this->now = now()->toDateTimeString();
        $this->invoiceColumn = $this->getMapField('invoice_column');
        $this->paymentMethods = $this->school->paymentMethods
            ->reduce(function (Collection $methods, PaymentMethod $method) {
                $driver = $method->getDriver();
                collect([$method->id, ...$driver->getImportDetectionValues()])
                    ->each(fn ($value) => $methods->put($value, $method->id));

                return $methods;
            }, collect());
    }

    public static function make(PaymentImport $import, User $user): static
    {
        return (new static($import, $user))
            ->setInvoices();
    }

    public function setInvoices(): static
    {
        $invoiceNumbers = $this->contents
            ->reject(fn ($row) => !$row[$this->invoiceColumn])
            ->pluck($this->invoiceColumn)
            ->map(fn ($number) => strtoupper($number));
//        $this->invoices = DB::table('invoices')
//            ->where('school_id', $this->school->id)
//            ->whereIn('invoice_number', $invoiceNumbers)
//            ->select(['uuid', 'remaining_balance', 'invoice_number', 'parent_uuid'])
//            ->get()
//            ->keyBy('invoice_number');
        $this->invoices = $this->school->invoices()
            ->whereIn('invoice_number', $invoiceNumbers)
            ->select(['uuid', 'remaining_balance', 'invoice_number', 'is_parent', 'parent_uuid'])
            ->with('children:uuid,remaining_balance,parent_uuid')
            ->get()
            ->keyBy('invoice_number');

        return $this;
    }

    public function asModels(bool $asModels): static
    {
        $this->asModels = $asModels;

        return $this;
    }

    protected function getCurrentRowInvoice(): ?Invoice
    {
        return $this->invoices->get(
            strtoupper($this->currentRow->get($this->invoiceColumn))
        );
    }

    protected function addResult(string $result, bool $successful = true)
    {
        $property = $successful ? 'importedRecords' : 'failedRecords';
        $this->{$property}++;

        $invoice = $this->getCurrentRowInvoice();

        $this->results->push([
            'row' => $this->currentRowNumber,
            'successful' => $successful,
            'result' => $result,
            'invoice' => $invoice?->invoice_number,
            'remaining_balance' => $invoice?->remaining_balance,
            'warnings' => [],
        ]);

//        $this->warnings = [];
    }

    protected function convertPaymentMethod($value): ?int
    {
        return $this->paymentMethods->get($value);
    }

    public function build()
    {
        foreach ($this->contents as $row) {
            $this->currentRowNumber++;
            $this->currentRow = $row;

            if (!$row[$this->invoiceColumn] ?? null) {
                // __('Missing invoice number');
                $this->addResult('Missing invoice number', false);
                continue;
            }

            $invoice = $this->getCurrentRowInvoice();

            if (!$invoice) {
                // __('Could not find invoice - invalid invoice number');
                $this->addResult('Could not find invoice - invalid invoice number', false);
                continue;
            }

            $amount = $this->getMapValue('amount', 'currency', 0);

            if (!$amount) {
                // __('Invalid amount');
                $this->addResult('Invalid amount', false);
                continue;
            }

            if ($amount > $invoice->remaining_balance) {
                // __('Payment greater than remaining balance');
                $this->addResult('Payment greater than remaining balance', false);
                continue;
            }

            $attributes = [
                'uuid' => UuidFactory::make(),
                'invoice_uuid' => $invoice->uuid,
                'amount' => $amount,
                'original_amount' => $amount,
                'school_id' => $this->school->id,
                'tenant_id' => $this->school->tenant_id,
                'paid_at' => $this->getMapValue('paid_at', 'date'),
                'payment_method_id' => $this->getMapValue('paid_at', 'payment method'),
                'notes' => $this->getMapValue('paid_at', 'notes'),
                'transaction_details' => $this->getMapValue('paid_at', 'transaction_details'),
//                'made_by' => $payment->made_by,
                'recorded_by' => $this->user->uuid,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];

            $this->invoicePayments->push($attributes);

            // We need to add the child payments for the children
            // after we've already pushed the parent payment details
            if ($invoice->children->isNotEmpty()) {
                $childPayments = $invoice->getChildrenPayments(new InvoicePayment($attributes));

                foreach ($childPayments as $childAttributes) {
                    $this->invoicePayments->push($childAttributes);
                }
            }

            // Update the invoice's remaining balance in the event that
            // there are more payments to this invoice in the same import,
            // so we need to keep a running balance of the import in
            // the context of these payments
            $invoice->remaining_balance = $invoice->remaining_balance - $amount;
            $this->invoices->put($invoice->invoice_number, $invoice);

            // __('Payment recorded successfully');
            $this->addResult('Payment recorded successfully');
        }

        ray($this->results->toArray());
        return $this->store();
    }

    protected function store()
    {
        DB::transaction(function () {
            DB::table('invoice_payments')
                ->insert($this->invoicePayments->toArray());

            // Create batch to calculate payments
            $batch = Bus::batch(
                    $this->invoicePayments
                        ->pluck('invoice_uuid')
                        ->unique()
                        ->map(fn ($uuid) => new SetInvoiceRemainingBalance($uuid))
                )->then(function (Batch $batch) {
                    //
                })->catch(function (Batch $batch, \Throwable $e) {
                    //
                })->finally(function (Batch $batch) {
                    $this->import->update(['job_batch_id' => null]);
                })
                ->name("Payment import {$this->import->id}")
                ->dispatch();

            $this->import->update([
                'results' => $this->results->toArray(),
                'imported_at' => now(),
                'job_batch_id' => $batch->id,
                'rolled_back_at' => null,
                'imported_records' => $this->importedRecords,
                'failed_records' => $this->failedRecords,
            ]);
        });
    }
}
