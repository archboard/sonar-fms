<?php

namespace App\Factories;

use App\Models\PaymentImport;
use Illuminate\Bus\Batch;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class PaymentFromImportFactory
{
    protected Collection $invoices;
    protected Collection $contents;
    protected Collection $invoicePayments;
    protected Collection $results;
    protected Collection $currentRow;
    protected bool $asModels = false;
    protected string $now;
    protected string $invoiceColumn;
    protected int $currentRowNumber = 0;
    protected int $failedRecords = 0;
    protected int $importedRecords = 0;

    public function __construct(protected PaymentImport $import)
    {
        $this->invoices = collect();
        $this->invoicePayments = collect();
        $this->contents = $this->import->getImportContents();
        $this->now = now()->toDateTimeString();
        $this->invoiceColumn = $this->getMapField('invoice_column');
    }

    public static function make(PaymentImport $import): static
    {
        return (new static($import))
            ->setInvoices();
    }

    public function setInvoices(): static
    {
        $invoiceNumbers = $this->contents
            ->filter(fn ($row) => !!$row[$this->invoiceColumn])
            ->pluck($this->invoiceColumn)
            ->map(fn ($number) => strtoupper($number));
        $this->invoices = DB::table('invoices')
            ->where('school_id', $this->import->school_id)
            ->whereIn('invoice_number', $invoiceNumbers)
            ->select(['uuid', 'remaining_balance', 'invoice_number', 'parent_uuid'])
            ->get()
            ->keyBy('invoice_number');

        return $this;
    }

    public function asModels(bool $asModels): static
    {
        $this->asModels = $asModels;

        return $this;
    }

    protected function getMapField(string $property): string|array
    {
        return Arr::get($this->import->mapping, $property);
    }

    protected function getCurrentRowInvoice(): ?\stdClass
    {
        return $this->invoices->get(
            $this->currentRow->get($this->invoiceColumn)
        );
    }

    protected function addResult(string $result, bool $successful = true)
    {
        $property = $successful ? 'importedRecords' : 'failedRecords';
        $this->{$property}++;

        $invoice = $this->getCurrentRowInvoice();

        $this->results[] = [
            'row' => $this->currentRowNumber,
            'successful' => $successful,
            'result' => $result,
            'invoice' => $invoice?->uuid,
            'remaining_balance' => $invoice?->remaining_balance,
            'warnings' => [],
        ];

//        $this->warnings = [];
    }

    public function build()
    {
        foreach ($this->contents as $row) {
            $this->currentRowNumber++;
            $this->currentRow = $row;

            if (!$row[$this->invoiceColumn] ?? null) {
                // __('Missing invoice number');
                $this->addResult('Missing invoice number', false);
            }

            $invoice = $this->getCurrentRowInvoice();

            if (!$invoice) {
                // __('Could not find invoice - invalid invoice number');
                $this->addResult('Could not find invoice - invalid invoice number', false);
            }

            $paymentUuid = UuidFactory::make();
        }
    }

    protected function store()
    {
        DB::transaction(function () {
            DB::table('invoice_payments')
                ->insert($this->invoicePayments->toArray());

            // Create batch to calculate payments
            /** @var Batch $batch */
            $batch = Bus::batch(
                $this->invoicePayments
                    ->pluck('invoice_uuid')
                    ->unique()
            )->then(function (Batch $batch) {
                //
            })->catch(function (Batch $batch, \Throwable $e) {
                //
            })->finally(function (Batch $batch) {
                $this->import->update(['job_batch_id' => null]);
            });

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
