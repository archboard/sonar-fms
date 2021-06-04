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
 * App\Models\Invoice
 *
 * @mixin IdeHelperInvoice
 * @property int $id
 * @property string $uuid
 * @property string|null $batch_id
 * @property string|null $import_id
 * @property int $tenant_id
 * @property int $school_id
 * @property int $student_id
 * @property int|null $term_id
 * @property string $title
 * @property string|null $description
 * @property int|null $amount_due
 * @property int|null $remaining_balance
 * @property Carbon|null $available_at
 * @property Carbon|null $due_at
 * @property Carbon|null $paid_at
 * @property Carbon|null $voided_at
 * @property bool $notify
 * @property Carbon|null $notify_at
 * @property Carbon|null $notified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $amount_due_formatted
 * @property-read bool $available
 * @property-read mixed $past_due
 * @property-read mixed $payment_made
 * @property-read mixed $remaining_balance_formatted
 * @property-read mixed $status_color
 * @property-read mixed $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceItem[] $invoiceItems
 * @property-read int|null $invoice_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceScholarship[] $invoiceScholarships
 * @property-read int|null $invoice_scholarships_count
 * @property-read \App\Models\School $school
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Tenant $tenant
 * @property-read \App\Models\Term|null $term
 * @method static \Database\Factories\InvoiceFactory factory(...$parameters)
 * @method static Builder|Invoice filter(array $filters)
 * @method static Builder|Invoice newModelQuery()
 * @method static Builder|Invoice newQuery()
 * @method static Builder|Invoice query()
 * @method static Builder|Invoice whereAmountDue($value)
 * @method static Builder|Invoice whereAvailableAt($value)
 * @method static Builder|Invoice whereBatchId($value)
 * @method static Builder|Invoice whereCreatedAt($value)
 * @method static Builder|Invoice whereDescription($value)
 * @method static Builder|Invoice whereDueAt($value)
 * @method static Builder|Invoice whereId($value)
 * @method static Builder|Invoice whereImportId($value)
 * @method static Builder|Invoice whereNotifiedAt($value)
 * @method static Builder|Invoice whereNotify($value)
 * @method static Builder|Invoice whereNotifyAt($value)
 * @method static Builder|Invoice wherePaidAt($value)
 * @method static Builder|Invoice whereRemainingBalance($value)
 * @method static Builder|Invoice whereSchoolId($value)
 * @method static Builder|Invoice whereStudentId($value)
 * @method static Builder|Invoice whereTenantId($value)
 * @method static Builder|Invoice whereTermId($value)
 * @method static Builder|Invoice whereTitle($value)
 * @method static Builder|Invoice whereUpdatedAt($value)
 * @method static Builder|Invoice whereUuid($value)
 * @method static Builder|Invoice whereVoidedAt($value)
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

    public static function createFromRequest(CreateInvoiceRequest $request, Student $student): static
    {
        // Generate generic invoice data
        // 1. The basic attributes
        // 2. Calculate the the total based on items

        // Generate generic items data
        // There are no fields dependent on other entity data

        // Generate generic scholarship data
        // 1. Basic attributes
        // 2. Calculated cached fields based on total or items
        $attributes = static::getAttributesFromRequest($request, $student);
        $uuid = $attributes['uuid'];
        $data = $request->validated();
        $school = $request->school();

        // This stores the the table name as the key
        // and the entries to be inserted as the value
        $tableData = [
            'invoice' => [$attributes],
            'invoice_items' => [],
            'invoice_scholarships' => [],
            'invoice_item_invoice_scholarship' => [],
            'item_id_map' => [],
        ];

        $fees = $school->fees->keyBy('id');

        // This generates all the items to be inserted in a single sql statement
        // and the id => uuid map that is needed for scholarship's `applies_to`
        $tableData = collect($data['items'])
            ->reduce(function (array $data, array $item) use ($uuid, $fees) {
                $attributes = InvoiceItem::generateAttributesForInsert($uuid, $item, $fees);

                $data['invoice_items'][] = $attributes;
                $data['item_id_map'][$item['id']] = $attributes['uuid'];

                return $data;
            }, $tableData);

        if (!empty($data['scholarships'])) {
            $scholarships = $school->scholarships->keyBy('id');

            $scholarshipItems = collect($data['scholarships'])
                ->reduce(function (array $inserts, array $item) use ($uuid, $scholarships, $itemData) {
                    $attributes = InvoiceScholarship::generateAttributesForInsert($uuid, $item, $scholarships);

                    $inserts['scholarships'][] = $attributes;

                    if (!empty($item['applies_to'])) {
                        $items = $invoiceItems
                            ->filter(fn ($i) => in_array($i['id'], $item['applies_to']))
                            ->map(fn ($i) => ['invoice_item_uuid' => $i['uuid'], 'invoice_scholarship_uuid' => $attributes['uuid']])
                            ->toArray();

                        $inserts['applies'] = array_merge($inserts['applies'], $items);
                    }

                    return $inserts;
                }, ['scholarships' => [], 'applies' => []]);

            DB::table('invoice_scholarships')
                ->insert($scholarshipItems->get('scholarships'));

            DB::table('invoice_item_invoice_scholarship')
                ->insert($scholarshipItems->get('applies'));
        }

        // Trigger the notification if it is set to queue
        if ($attributes['notify']) {
            $this->notifyLater();
        }
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
