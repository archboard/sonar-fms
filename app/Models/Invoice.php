<?php

namespace App\Models;

use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Jobs\SendNewInvoiceNotification;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use Brick\Money\Money;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * @mixin IdeHelperInvoice
 */
class Invoice extends Model
{
    use BelongsToTenant;
    use BelongsToSchool;
    use HasFactory;
    use HasResource;

    protected $fillable = [
        'uuid',
        'batch_id',
        'import_id',
        'tenant_id',
        'school_id',
        'student_id',
        'term_id',
        'title',
        'description',
        'amount_due',
        'remaining_balance',
        'invoice_date',
        'available_at',
        'due_at',
        'paid_at',
        'voided_at',
        'notify',
        'notify_at',
        'notified_at',
    ];

    protected $casts = [
        'notify_now' => 'boolean',
        'due_at' => 'datetime',
        'voided_at' => 'datetime',
        'paid_at' => 'datetime',
        'notify_at' => 'datetime',
        'notified_at' => 'datetime',
        'available_at' => 'datetime',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, $search) {
            $builder->where(function (Builder $builder) use ($search) {
                $builder->where('id', 'ilike', "{$search}%")
                    ->orWhere('title', 'ilike', "%{$search}%");
            });
        });

        $orderBy = $filters['orderBy'] ?? 'title';
        $orderDir = $filters['orderDir'] ?? 'asc';

        $builder->orderBy($orderBy, $orderDir);
        $builder->orderBy('invoices.title', $orderDir);
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
        return $this->hasMany(InvoiceItem::class, 'invoice_uuid', 'uuid');
    }

    public function invoiceScholarships(): HasMany
    {
        return $this->hasMany(InvoiceScholarship::class, 'invoice_uuid', 'uuid');
    }

    public function getAmountDueFormattedAttribute()
    {
        if (
            !$this->relationLoaded('school') ||
            !$this->school->relationLoaded('currency')
        ) {
            return null;
        }

        return Money::ofMinor($this->amount_due, $this->school->currency->code)
            ->formatTo(optional(auth()->user())->locale ?? 'en');
    }

    public function getRemainingBalanceFormattedAttribute()
    {
        if (
            !$this->relationLoaded('school') ||
            !$this->school->relationLoaded('currency')
        ) {
            return null;
        }

        return Money::ofMinor($this->remaining_balance, $this->school->currency->code)
            ->formatTo(optional(auth()->user())->locale ?? 'en');
    }

    public function getStatusColorAttribute()
    {
        if ($this->paid_at) {
            return 'green';
        }

        if ($this->payment_made || !$this->available) {
            return 'yellow';
        }

        if ($this->past_due) {
            return 'red';
        }

        if (!$this->paid_at) {
            return 'yellow';
        }

        return 'gray';
    }

    public function getStatusLabelAttribute()
    {
        if ($this->paid_at) {
            return __('Paid');
        }

        if ($this->voided_at) {
            return __('Void');
        }

        if ($this->payment_made) {
            return __('Partially paid');
        }

        if ($this->past_due) {
            return __('Past due');
        }

        if (!$this->available) {
            return __('Unavailable');
        }

        return __('Unpaid');
    }

    public function getPaymentMadeAttribute()
    {
        return $this->amount_due !== $this->remaining_balance;
    }

    public function getPastDueAttribute()
    {
        return $this->due_at && now() > $this->due_at;
    }

    public function getAvailableAttribute(): bool
    {
        if (!$this->available_at) {
            return true;
        }

        return now() >= $this->available_at;
    }

    public function fullLoad(): static
    {
        return $this->load([
            'school',
            'school.currency',
            'invoiceItems',
            'invoiceItems.invoice',
            'invoiceItems.invoice.school',
            'invoiceItems.invoice.school.currency',
            'invoiceScholarships',
            'invoiceScholarships.invoice',
            'invoiceScholarships.invoice.school',
            'invoiceScholarships.invoice.school.currency',
        ]);
    }

    public static function getAttributesFromRequest(CreateInvoiceRequest $request, Student $student = null): array
    {
        $school = $request->school();
        $data = $request->validated();
        $invoiceAttributes = Arr::except($data, ['items', 'scholarships']);

        $invoiceAttributes['uuid'] = Uuid::uuid4();
        $invoiceAttributes['tenant_id'] = $school->tenant_id;
        $invoiceAttributes['school_id'] = $school->id;
        $invoiceAttributes['student_id'] = optional($student)->id;
        $total = static::getSubmittedItemsTotal($data['items'], $school->fees->keyBy('id'));

        $invoiceAttributes['amount_due'] = $total;
        $invoiceAttributes['remaining_balance'] = $total;

        return $invoiceAttributes;
    }

    public static function calculateSubtotalFromItems(Collection $items)
    {
        return $items
            ->reduce(function (int $total, InvoiceItem $item) {
                return $total + $item->calculateTotal();
            }, 0);
    }

    public function calculateSubtotal(): int
    {
        return static::calculateSubtotalFromItems($this->invoiceItems);
    }

    public function calculateScholarshipSubtotal()
    {
        return $this->invoiceScholarships
            ->reduce(function (int $total, InvoiceScholarship $scholarship) {
                return $total + $scholarship->calculateAmount();
            }, 0);
    }

    /**
     * Sets the amount due, remaining balance, and paid time stamp.
     * Does not save in the database.
     *
     * @return $this
     */
    public function setAmountDue(): static
    {
        $subtotal = $this->calculateSubtotal();
        $discount = $this->calculateScholarshipSubtotal();

        $amountDue = $subtotal - $discount;

        if ($amountDue < 0) {
            $amountDue = 0;
        }

        // Calculate how much has already been paid in
        // and set the remaining_balance value based on that
        $paid = 0;

        $remaining = $amountDue - $paid;

        $this->forceFill([
            'amount_due' => $amountDue,
            'remaining_balance' => $remaining,
            'paid_at' => $remaining === 0 ? now() : null,
        ]);

        return $this;
    }

    public function cacheCalculations()
    {
        // Cache all items
        $this->invoiceItems->each(function (InvoiceItem $item) {
            $item->update([
                'amount' => $item->calculateTotal(),
            ]);
        });

        // Cache all scholarship calculations
        $scholarships = $this->invoiceScholarships()
            ->with('invoice', 'invoice.invoiceItems')
            ->get();
        $scholarships->each(function (InvoiceScholarship $scholarship) {
            $scholarship->update([
                'calculated_amount' => $scholarship->calculateAmount(),
            ]);
        });

        $this->setAmountDue()->save();
    }

    public function queueNotification(Carbon $notifyAt): static
    {
        $notifyAt->startOfMinute();

        $this->update([
            'notify' => true,
            'notify_at' => $notifyAt,
            'notified_at' => null,
        ]);

        // Dispatch the notification for 15 minutes
        SendNewInvoiceNotification::dispatch($this->uuid)
            ->delay($notifyAt);

        return $this;
    }

    public function notifyLater(Carbon $dateTime = null): static
    {
        return $this->queueNotification($dateTime ?? now()->addMinutes(15));
    }

    public function notifyNow(): static
    {
        return $this->queueNotification(now());
    }

    public function cancelNotification(): static
    {
        $this->update([
            'notify' => false,
            'notify_at' => null,
            'notified_at' => null,
        ]);

        return $this;
    }

    public function updateFromRequest(UpdateInvoiceRequest $request): static
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            $school = $request->school();

            $existingItems = $this->invoiceItems->keyBy('id');
            $existingScholarshipItems = $this->invoiceScholarships->keyBy('id');

            // Contains the submitted items for the invoice
            $items = collect($data['items']);

            // Delete items that aren't present on the request
            $missingItems = $this->invoiceItems
                ->filter(fn ($item) => !$items->contains('id', $item->id));

            if ($missingItems->isNotEmpty()) {
                $this->invoiceItems()
                    ->whereIn('id', $missingItems->pluck('id'))
                    ->delete();
            }

            // Update the items
            $fees = $school->fees->keyBy('id');
            $newItems = $items
                ->reduce(function (array $newItems, $item) use ($existingItems, $fees) {
                    if ($existingItem = $existingItems->get($item['id'])) {
                        $existingItem->update($item);
                        return $newItems;
                    }

                    $newItems[] = InvoiceItem::generateAttributesForInsert(
                        $this->uuid,
                        $item,
                        $fees
                    );

                    return $newItems;
                }, []);

            if (!empty($newItems)) {
                DB::table('invoice_items')
                    ->insert($newItems);
            }

            $scholarshipItems = collect($data['scholarships']);

            // Delete scholarships not present in the request
            $missingScholarships = $this->invoiceScholarships
                ->filter(fn ($item) => !$scholarshipItems->contains('id', $item->id));

            if ($missingScholarships->isNotEmpty()) {
                $this->invoiceScholarships()
                    ->whereIn('id', $missingScholarships->pluck('id'))
                    ->delete();
            }

            // Update the scholarships
            $scholarships = $school->scholarships->keyBy('id');
            $newScholarshipItems = $scholarshipItems
                ->reduce(function (array $newItems, array $item) use ($existingScholarshipItems, $scholarships) {
                    if ($existingItem = $existingScholarshipItems->get($item['id'])) {
                        $existingItem->update($item);
                        return $newItems;
                    }

                    $newItems[] = InvoiceScholarship::generateAttributesForInsert(
                        $this->uuid,
                        $item,
                        $scholarships
                    );

                    return $newItems;
                }, []);

            if (!empty($newScholarshipItems)) {
                DB::table('invoice_scholarships')
                    ->insert($newScholarshipItems);
            }

            $this->unsetRelations();
            $this->setAmountDue()
                ->forceFill(Arr::except($data, ['items', 'scholarships']))
                ->save();
        });

        return $this;
    }

    public static function getSubmittedItemsTotal(Collection $items, Collection $fees): int
    {
        return $items->reduce(function (int $total, array $item) use ($fees) {
            if ($item['sync_with_fee'] && $fee = $fees->get($item['fee_id'])) {
                $item['amount_per_unit'] = $fee->amount;
            }

            return $total + ($item['amount_per_unit'] * $item['quantity']);
        }, 0);
    }
}
