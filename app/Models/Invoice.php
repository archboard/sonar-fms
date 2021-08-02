<?php

namespace App\Models;

use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Jobs\SendNewInvoiceNotification;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;
use App\Traits\HasTaxRateAttribute;
use App\Traits\UsesUuid;
use Brick\Money\Money;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
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
    use BelongsToUser;
    use UsesUuid;
    use HasFactory;
    use HasResource;
    use HasTaxRateAttribute;

    protected $fillable = [
        'uuid',
        'batch_id',
        'import_id',
        'tenant_id',
        'school_id',
        'student_id',
        'term_id',
        'invoice_layout_id',
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
        'apply_tax',
        'use_school_tax_defaults',
        'tax_rate',
        'tax_label',
        'tax_due',
        'pre_tax_subtotal',
    ];

    protected $casts = [
        'notify_now' => 'boolean',
        'apply_tax' => 'boolean',
        'use_school_tax_defaults' => 'boolean',
        'tax_rate' => 'float',
        'due_at' => 'datetime',
        'voided_at' => 'datetime',
        'paid_at' => 'datetime',
        'notify_at' => 'datetime',
        'notified_at' => 'datetime',
        'available_at' => 'datetime',
    ];

    protected $keyType = 'string';

    // These are the attributes/properties that are
    // used on the invoice form based on the API Resource
    public static array $formAttributes = [
        'title',
        'description',
        'term_id',
        'available_at',
        'due_at',
        'notify',
        'items',
        'scholarships',
        'payment_schedules',
        'apply_tax',
        'use_school_tax_defaults',
        'tax_rate',
        'tax_label',
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

    public function invoicePaymentSchedules(): HasMany
    {
        return $this->hasMany(InvoicePaymentSchedule::class, 'invoice_uuid', 'uuid');
    }

    public function invoicePaymentTerms(): HasMany
    {
        return $this->hasMany(InvoicePaymentTerm::class, 'invoice_uuid', 'uuid');
    }

    public function invoiceImport(): BelongsTo
    {
        return $this->belongsTo(InvoiceImport::class, 'import_id');
    }

    public function invoiceLayout(): BelongsTo
    {
        return $this->belongsTo(InvoiceLayout::class);
    }

    public function getAmountDueFormattedAttribute(): ?string
    {
        if (!$this->relationLoaded('currency')) {
            return null;
        }

        return displayCurrency($this->amount_due, $this->currency);
    }

    public function getTaxDueFormattedAttribute(): ?string
    {
        if (!$this->relationLoaded('currency')) {
            return null;
        }

        return displayCurrency($this->tax_due, $this->currency);
    }

    public function getSubtotalFormattedAttribute(): ?string
    {
        if (!$this->relationLoaded('currency')) {
            return null;
        }

        return displayCurrency($this->subtotal, $this->currency);
    }

    public function getDiscountTotalFormattedAttribute(): ?string
    {
        if (!$this->relationLoaded('currency')) {
            return null;
        }

        return displayCurrency($this->discount_total, $this->currency);
    }

    public function getRemainingBalanceFormattedAttribute(): ?string
    {
        if (!$this->relationLoaded('currency')) {
            return null;
        }

        return displayCurrency($this->remaining_balance, $this->currency);
    }

    public function getNumberFormattedAttribute(): string
    {
        return '#' . $this->id;
    }

    public function getStatusColorAttribute(): string
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

    public function getStatusLabelAttribute(): string
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

    public function getPaymentMadeAttribute(): bool
    {
        return $this->amount_due !== $this->remaining_balance;
    }

    public function getPastDueAttribute(): bool
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
            'student',
            'school',
            'currency',
            'invoiceItems.invoice.currency',
            'invoiceScholarships.invoice.currency',
            'invoicePaymentSchedules',
            'invoicePaymentSchedules.invoicePaymentTerms',
        ]);
    }

    public static function calculateSubtotalFromItems(Collection $items)
    {
        return $items
            ->reduce(function (int $total, InvoiceItem $item) {
                return $total + $item->calculateTotal();
            }, 0);
    }

    public function setSubtotal(): static
    {
        $this->subtotal = static::calculateSubtotalFromItems($this->invoiceItems);

        return $this;
    }

    public function setDiscountTotal(): static
    {
        $discount = $this->invoiceScholarships
            ->reduce(function (int $total, InvoiceScholarship $scholarship) {
                return $total + $scholarship->calculateAmount();
            }, 0);

        $this->discount_total = $discount > $this->subtotal
            ? $this->subtotal
            : $discount;

        return $this;
    }

    public function setPreTaxSubtotal(): static
    {
        $subtotal = $this->subtotal - $this->discount_total;

        $this->pre_tax_subtotal = $subtotal < 0
            ? 0
            : $subtotal;

        return $this;
    }

    public function setTaxDue(): static
    {
        $this->tax_due = 0;

        if (
            $this->school->collect_tax &&
            $this->apply_tax
        ) {
            $this->tax_due = round($this->pre_tax_subtotal * $this->tax_rate);
        }

        return $this;
    }

    public function setAmountDue(): static
    {
        $this->amount_due = $this->pre_tax_subtotal + $this->tax_due;

        return $this;
    }

    public function setRemainingBalance(): static
    {
        // Calculate how much has already been paid in
        // and set the remaining_balance value based on that
        $paid = 0;

        $this->remaining_balance = $this->amount_due - $paid;
        $this->paid_at = $this->remaining_balance < 0
            ? now()
            : null;

        return $this;
    }

    /**
     * Sets the amount due, remaining balance, and paid time stamp.
     * Does not save in the database.
     *
     * @return $this
     */
    public function setCalculatedAttributes(bool $save = false): static
    {
        $this->setSubtotal()
            ->setDiscountTotal()
            ->setPreTaxSubtotal()
            ->setTaxDue()
            ->setAmountDue()
            ->setRemainingBalance();

        if ($save) {
            $this->save();
        }

        return $this;
    }

    public function cacheCalculations()
    {
        // Cache all items
        $this->invoiceItems->each(function (InvoiceItem $item) {
            $item->setAmount()->save();
        });

        // Cache all scholarship calculations
        $scholarships = $this->invoiceScholarships()
            ->with('invoice', 'invoice.invoiceItems')
            ->get();
        $scholarships->each(function (InvoiceScholarship $scholarship) {
            $scholarship->setAmount()->save();
        });

        $this->setCalculatedAttributes()->save();
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
            $this->setCalculatedAttributes()
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

    /**
     * Takes the invoice and prunes all the fields
     * that aren't required to create an invoice
     *
     * @return array
     */
    public function asInvoiceTemplate(): array
    {
        $this->load(
            'invoiceItems',
            'invoiceScholarships',
            'invoiceScholarships.appliesTo',
            'invoicePaymentSchedules',
            'invoicePaymentSchedules.invoicePaymentTerms'
        );

        $resource = $this->toResource()
            ->response()
            ->getData(true);

        $data = Arr::only($resource, static::$formAttributes);

        $data['items'] = array_map(
            fn ($item) => Arr::only($item, InvoiceItem::$formAttributes),
            $data['items'],
        );

        $data['scholarships'] = array_map(
            function ($item) {
                $item['percentage'] = $item['percentage_converted'];

                return Arr::only($item, InvoiceScholarship::$formAttributes);
            },
            $data['scholarships']
        );

        $data['payment_schedules'] = array_map(
            function ($schedule) {
                $schedule['terms'] = array_map(
                    fn ($term) => Arr::only($term, InvoicePaymentTerm::$formAttributes),
                    $schedule['terms']
                );

                return Arr::only($schedule, InvoicePaymentSchedule::$formAttributes);
            },
            $data['payment_schedules']
        );

        return $data;
    }

    public function convertToInvoiceTemplate(array $data): InvoiceTemplate
    {
        return InvoiceTemplate::create([
            'school_id' => $this->school_id,
            'user_id' => auth()->id(),
            'name' => $data['name'] ?? "Created from invoice {$this->number_formatted}",
            'template' => $this->asInvoiceTemplate(),
            'for_import' => false,
        ]);
    }
}
