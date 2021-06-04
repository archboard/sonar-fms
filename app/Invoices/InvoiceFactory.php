<?php

namespace App\Invoices;

use App\Jobs\SendNewInvoiceNotification;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

    protected array $fillableInvoiceAttributes;
    protected array $fillableInvoiceItemAttributes;
    protected array $fillableScholarshipAttributes;

    public function __construct()
    {
        $this->invoices = collect();
        $this->invoiceItems = collect();
        $this->invoiceScholarships = collect();
        $this->itemScholarshipPivot = collect();

        $this->fillableInvoiceAttributes = (new Invoice)->getFillable();
        $this->fillableInvoiceItemAttributes = (new InvoiceItem)->getFillable();
        $this->fillableScholarshipAttributes = (new InvoiceScholarship)->getFillable();
    }

    protected function uuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    protected function cleanInvoiceAttributes(array $attributes): array
    {
        return Arr::only($attributes, $this->fillableInvoiceAttributes);
    }

    protected function cleanInvoiceItemAttributes(array $attributes): array
    {
        return Arr::only($attributes, $this->fillableInvoiceItemAttributes);
    }

    protected function cleanInvoiceScholarshipAttributes(array $attributes): array
    {
        return Arr::only($attributes, $this->fillableScholarshipAttributes);
    }

    protected function store(): Collection
    {
        ray(
            'Data to insert',
            $this->invoices->toArray(),
            $this->invoiceItems->toArray(),
            $this->invoiceScholarships->toArray(),
            $this->itemScholarshipPivot->toArray()
        )->green();

        DB::transaction(function () {
            DB::table('invoices')
                ->insert($this->invoices->toArray());

            DB::table('invoice_items')
                ->insert($this->invoiceItems->toArray());

            DB::table('invoice_scholarships')
                ->insert($this->invoiceScholarships->toArray());

            DB::table('invoice_item_invoice_scholarship')
                ->insert($this->itemScholarshipPivot->toArray());
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
