<?php

namespace App\Factories;

use App\Jobs\SendNewInvoiceNotification;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use App\Models\InvoiceScholarship;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

abstract class InvoiceFactory
{
    public ?School $school = null;

    // These are the collections that store the attributes
    // that need to be stored in the db
    protected Collection $invoices;
    protected Collection $invoiceItems;
    protected Collection $invoiceScholarships;
    protected Collection $itemScholarshipPivot;
    protected Collection $invoicePaymentSchedules;
    protected Collection $invoicePaymentTerms;

    protected array $fillableInvoiceAttributes;
    protected array $fillableInvoiceItemAttributes;
    protected array $fillableScholarshipAttributes;
    protected array $fillablePaymentScheduleAttributes;
    protected array $fillablePaymentTermAttributes;

    protected string $now;

    public function __construct()
    {
        $this->invoices = collect();
        $this->invoiceItems = collect();
        $this->invoiceScholarships = collect();
        $this->itemScholarshipPivot = collect();
        $this->invoicePaymentSchedules = collect();
        $this->invoicePaymentTerms = collect();

        $this->fillableInvoiceAttributes = (new Invoice)->getFillable();
        $this->fillableInvoiceItemAttributes = (new InvoiceItem)->getFillable();
        $this->fillableScholarshipAttributes = (new InvoiceScholarship)->getFillable();
        $this->fillablePaymentScheduleAttributes = (new InvoicePaymentSchedule)->getFillable();
        $this->fillablePaymentTermAttributes = (new InvoicePaymentTerm)->getFillable();

        $this->now = now()->toDateTimeString();
    }

    protected function uuid(): string
    {
        return UuidFactory::make();
    }

    protected function cleanAttributes(array $attributes, array $fillable, array $additional = ['created_at', 'updated_at']): array
    {
        $allowed = array_merge($fillable, $additional);

        return Arr::only($attributes, $allowed);
    }

    protected function cleanInvoiceAttributes(array $attributes): array
    {
        return $this->cleanAttributes($attributes, $this->fillableInvoiceAttributes);
    }

    protected function cleanInvoiceItemAttributes(array $attributes): array
    {
        return $this->cleanAttributes($attributes, $this->fillableInvoiceItemAttributes);
    }

    protected function cleanInvoiceScholarshipAttributes(array $attributes): array
    {
        return $this->cleanAttributes($attributes, $this->fillableScholarshipAttributes);
    }

    protected function cleanPaymentScheduleAttributes(array $attributes): array
    {
        return $this->cleanAttributes($attributes, $this->fillablePaymentScheduleAttributes);
    }

    protected function cleanPaymentTermAttributes(array $attributes): array
    {
        return $this->cleanAttributes($attributes, $this->fillablePaymentTermAttributes);
    }

    protected function store(): Collection
    {
        ray(
            'Data to insert',
            $this->invoices->toArray(),
            $this->invoiceItems->toArray(),
            $this->invoiceScholarships->toArray(),
            $this->itemScholarshipPivot->toArray(),
            $this->invoicePaymentSchedules->toArray(),
            $this->invoicePaymentTerms->toArray()
        )->green();

        DB::transaction(function () {
            DB::table('invoices')
                ->insert($this->invoices->toArray());

            DB::table('invoice_items')
                ->insert($this->invoiceItems->toArray());

            if ($this->invoiceScholarships->isNotEmpty()) {
                DB::table('invoice_scholarships')
                    ->insert($this->invoiceScholarships->toArray());
            }

            if ($this->itemScholarshipPivot->isNotEmpty()) {
                DB::table('invoice_item_invoice_scholarship')
                    ->insert($this->itemScholarshipPivot->toArray());
            }

            if ($this->invoicePaymentSchedules->isNotEmpty()) {
                DB::table('invoice_payment_schedules')
                    ->insert($this->invoicePaymentSchedules->toArray());

                DB::table('invoice_payment_terms')
                    ->insert($this->invoicePaymentTerms->toArray());
            }
        });

        return $this->invoices->map(function (array $invoice) {
            if ($invoice['notify']) {
                SendNewInvoiceNotification::dispatch($invoice['uuid'])
                    ->delay(Carbon::parse($invoice['notify_at']));
            }

            return $invoice['uuid'];
        });
    }
}
