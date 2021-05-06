<?php

namespace App\Models;

use App\Http\Requests\CreateInvoiceRequest;
use App\Jobs\SendNewInvoiceNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

/**
 * @mixin IdeHelperInvoice
 */
class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'notify_now' => 'boolean',
        'due_at' => 'datetime',
        'voided_at' => 'datetime',
        'paid_at' => 'datetime',
        'notify_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->uuid = Uuid::uuid4();
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public static function createFromRequest(CreateInvoiceRequest $request, School $school, Student $student): static
    {
        $data = $request->validated();
        $invoiceAttributes = Arr::except($data, 'items');
        $invoiceAttributes['school_id'] = $school->id;

        if ($data['notify_now']) {
            $invoiceAttributes['notify_at'] = now()->addMinutes(15);
        }

        /** @var Invoice $invoice */
        $invoice = $student->invoices()
            ->create(Arr::except($invoiceAttributes, 'notify_now'));

        if ($invoice->notify_at) {
            // Dispatch the notification for 15 minutes
            SendNewInvoiceNotification::dispatch($invoice)
                ->delay($invoice->notify_at);
        }

        return $invoice;
    }

    /**
     * @param Collection $items The collection of items from CreateNewInvoiceRequest
     * @param Collection $fees The fees should be keyed by the id
     * @return array
     */
    public function getInvoiceItemAttributesForInsert(Collection $items, Collection $fees): array
    {
        return $items->map(function ($item) use ($fees) {
            $item['invoice_id'] = $this->id;

            if ($item['sync_with_fee']) {
                $fee = $fees->get($item['fee_id']);

                $item['name'] = $fee->name;
                $item['amount_per_unit'] = $fee->amount;
            }

            return $item;
        })->toArray();
    }
}
