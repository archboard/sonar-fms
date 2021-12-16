<?php

namespace App\Factories;

use App\Events\PaymentImportFinished;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentFromImportFactory extends BaseImportFactory
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
    protected Collection $models;
    protected Collection $activityLogs;
    protected Collection $invoicesToRecalculate;
    protected array $warnings = [];

    public function __construct(protected PaymentImport $import, protected User $user)
    {
        $this->school = $this->import->school;
        $this->invoices = collect();
        $this->invoicePayments = collect();
        $this->results = collect();
        $this->models = collect();
        $this->activityLogs = collect();
        $this->invoicesToRecalculate = collect();
        $this->contents = $this->import->getImportContents();
        $this->now = now()->toDateTimeString();
        $this->batchId = UuidFactory::make();
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
            ->setInvoices()
            ->setUsers();
    }

    public function setInvoices(): static
    {
        $invoiceNumbers = $this->contents
            ->reject(fn ($row) => !$row[$this->invoiceColumn])
            ->pluck($this->invoiceColumn)
            ->map(fn ($number) => strtoupper($number));
        $this->invoices = $this->school->invoices()
            ->whereIn('invoice_number', $invoiceNumbers)
            ->select([
                'uuid',
                'remaining_balance',
                'amount_due',
                'invoice_number',
                'is_parent',
                'parent_uuid',
            ])
            ->with('children:uuid,remaining_balance,parent_uuid')
            ->get()
            ->keyBy('invoice_number');

        return $this;
    }

    public function setUsers(): static
    {
        $this->users = collect();

        if ($madeByColumn = $this->getMapField('made_by.column')) {
            $emails = $this->contents->pluck($madeByColumn)
                ->filter(fn ($value) => $madeByColumn)
                ->unique()
                ->map(fn ($value) => strtolower($value));

            $this->users = User::whereIn('email', $emails)
                ->pluck('uuid', 'email');
        }

        return $this;
    }

    public function asModels(bool $asModels = true): static
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
            'invoice_uuid' => $invoice?->uuid,
            'remaining_balance' => $invoice?->remaining_balance,
            'warnings' => $this->warnings,
        ]);

        $this->warnings = [];
    }

    protected function convertPaymentMethod($value): ?int
    {
        return $this->paymentMethods->get($value);
    }

    protected function convertUserEmail(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_contains($value, '@')) {
            if ($user = $this->users->get($value)) {
                return $user;
            }

            $this->warnings[] = "Could not locate user from {$value}. Please ensure the email matches a user in the SIS.";

            return null;
        }

        return Str::isUuid($value)
            ? $value
            : null;
    }

    protected function queueToRecalculate($invoiceUuid): static
    {
        if (!$this->invoicesToRecalculate->contains($invoiceUuid)) {
            $this->invoicesToRecalculate->push($invoiceUuid);
        }

        return $this;
    }

    protected function addLog(array $attributes): static
    {
        if (!$this->logActivity) {
            return $this;
        }

        $defaultAttributes = [
            'log_name' => 'default',
            'causer_type' => 'user',
            'causer_id' => $this->user->id,
            'created_at' => $this->now,
            'updated_at' => $this->now,
            'batch_uuid' => $this->batchId,
        ];

        $this->activityLogs->push(array_merge($defaultAttributes, $attributes));

        return $this;
    }

    protected function addLogForPayment(array $attributes): static
    {
        return $this->addLog([
            // __(':user imported a payment of :amount')
            'description' => ':user imported a payment of :amount',
            'subject_type' => 'invoice',
            'subject_id' => $attributes['invoice_uuid'],
            'properties' => json_encode([
                'amount' => displayCurrency($attributes['amount'], $this->school->currency)
            ]),
        ]);
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
                'parent_uuid' => null,
                'invoice_uuid' => $invoice->uuid,
                'amount' => $amount,
                'original_amount' => $amount,
                'school_id' => $this->school->id,
                'tenant_id' => $this->school->tenant_id,
                'paid_at' => $this->getMapValue('paid_at', 'date'),
                'payment_method_id' => $this->getMapValue('payment_method', 'payment method'),
                'notes' => $this->getMapValue('notes', 'notes'),
                'transaction_details' => $this->getMapValue('transaction_details', 'transaction details'),
                'made_by' => $this->getMapValue('made_by', 'user email'),
                'payment_import_id' => $this->import->id,
                'recorded_by' => $this->user->uuid,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ];

            $this->invoicePayments->push($attributes);
            $this->queueToRecalculate($invoice->uuid)
                ->addLogForPayment($attributes);

            if ($invoice->parent_uuid) {
                $this->queueToRecalculate($invoice->parent_uuid);
                $this->addLog([
                    // __(':user imported a payment of :amount made to :invoice_number')
                    'description' => ':user imported a payment of :amount made to :invoice_number',
                    'subject_type' => 'invoice',
                    'subject_id' => $invoice->parent_uuid,
                    'properties' => json_encode([
                        'amount' => displayCurrency($attributes['amount'], $this->school->currency),
                        'invoice_number' => $invoice->invoice_number,
                    ]),
                ]);
            }

            // We need to add the child payments for the children
            // after we've already pushed the parent payment details
            if ($invoice->children->isNotEmpty()) {
                $childPayments = $invoice->getChildrenPayments(new InvoicePayment($attributes));
                $overwriteAttributes = [
                    'uuid',
                    'parent_uuid',
                    'invoice_uuid',
                    'amount',
                    'original_amount',
                ];

                foreach ($childPayments as $childAttributes) {
                    $child = array_merge($attributes, Arr::only($childAttributes, $overwriteAttributes));
                    $this->invoicePayments->push($child);
                    $this->addLogForPayment($child);
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

            if ($this->asModels) {
                $payment = new InvoicePayment($attributes);
                $payment->setRelation('invoice', $invoice);

                $this->models->push($payment);
            }
        }

        if ($this->asModels) {
            $this->import->results = $this->results;
            $this->import->failed_records = $this->failedRecords;
            $this->import->imported_records = $this->importedRecords;

            return collect()->put('paymentImport', $this->import)
                ->put('models', $this->models);
        }

        return $this->store();
    }

    protected function store(): Collection
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
                    event(new PaymentImportFinished($this->import));
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
                'import_batch_id' => $this->batchId,
            ]);

            // Add the activity logs
            DB::table(config('activitylog.table_name'))
                ->insert($this->activityLogs->toArray());
        });

        return $this->invoicePayments->pluck('uuid');
    }
}
