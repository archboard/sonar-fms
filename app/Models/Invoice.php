<?php

namespace App\Models;

use App\Http\Requests\UpdateInvoiceRequest;
use App\Jobs\SendNewInvoiceNotification;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;
use App\Traits\HasActivities;
use App\Traits\HasTaxRateAttribute;
use App\Traits\ScopeToCurrentSchool;
use App\Traits\UsesUuid;
use GrantHolle\Http\Resources\Traits\HasResource;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JamesMills\LaravelTimezone\Facades\Timezone;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

/**
 * @mixin IdeHelperInvoice
 */
class Invoice extends Model implements Searchable
{
    use BelongsToTenant;
    use BelongsToSchool;
    use BelongsToUser;
    use UsesUuid;
    use HasFactory;
    use HasResource;
    use HasTaxRateAttribute;
    use HasActivities;

    public const ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    protected $fillable = [
        'uuid',
        'batch_id',
        'import_id',
        'tenant_id',
        'school_id',
        'student_uuid',
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
        'parent_uuid',
        'published_at',
        'apply_tax_to_all_items',
        'relative_tax_rate',
        'invoice_number',
        'is_parent',
        'invoice_payment_schedule_uuid',
        'total_paid',
    ];

    protected $casts = [
        'notify_now' => 'boolean',
        'apply_tax' => 'boolean',
        'use_school_tax_defaults' => 'boolean',
        'apply_tax_to_all_items' => 'boolean',
        'tax_rate' => 'float',
        'relative_tax_rate' => 'float',
        'invoice_date' => 'date',
        'due_at' => 'datetime',
        'voided_at' => 'datetime',
        'paid_at' => 'datetime',
        'notify_at' => 'datetime',
        'notified_at' => 'datetime',
        'available_at' => 'datetime',
        'published_at' => 'datetime',
        'is_parent' => 'boolean',
        'total_paid' => 'int',
        'amount_due' => 'int',
        'remaining_balance' => 'int',
    ];

    // These are the attributes/properties that are
    // used on the invoice form based on the API Resource
    public static array $formAttributes = [
        'students',
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

    protected static function booted()
    {
        static::saving(function (Invoice $invoice) {
            if ($invoice->isDirty('voided_at')) {
                // __('Invoice voided by :user.')
                activity()
                    ->on($invoice)
                    ->log('Invoice voided by :user.');
            }

            if ($invoice->isDirty('published_at')) {
                // __('Invoice published by :user.')
                activity()
                    ->on($invoice)
                    ->log('Invoice published by :user.');
            }
        });
    }

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, $search) {
            $builder->where(function (Builder $builder) use ($search) {
                $builder->where('id', 'ilike', "{$search}%")
                    ->orWhere('title', 'ilike', "%{$search}%")
                    ->orWhere('invoice_number', 'ilike', "%{$search}%")
                    ->orWhereHas('student', function (Builder $builder) use ($search) {
                        $builder->filter(['s' => $search]);
                    })
                    ->orWhereHas('students', function (Builder $builder) use ($search) {
                        $builder->filter(['s' => $search]);
                    });
            });
        })->when($filters['batch_id'] ?? null, function (Builder $builder, $batchId) {
            $builder->where('batch_id', $batchId);
        })->when($filters['ids'] ?? null, function (Builder $builder, $ids) {
            if (is_array($ids)) {
                $builder->whereIn('uuid', $ids);
            } else {
                $builder->where('uuid', $ids);
            }
        })->when($filters['status'] ?? null, function (Builder $builder, $statuses) {
            if (empty($statuses)) {
                return;
            }

            $builder->where(function (Builder $builder) use ($statuses) {
                if (in_array('unpaid', $statuses)) {
                    $builder->orWhere(function (Builder $builder) {
                        $builder->whereNull('paid_at')
                            ->whereNull('voided_at');
                    });
                }
                if (in_array('paid', $statuses)) {
                    $builder->orWhereNotNull('paid_at');
                }
                if (in_array('published', $statuses)) {
                    $builder->orWhere(function (Builder $builder) {
                        $builder->whereNotNull('published_at')
                            ->whereNull('voided_at');
                    });
                }
                if (in_array('draft', $statuses)) {
                    $builder->orWhereNull('published_at');
                }
                if (in_array('past', $statuses)) {
                    $builder->orWhere(function (Builder $builder) {
                        $builder->where('due_at', '<', now())
                            ->whereNull('voided_at');
                    });
                }
                if (in_array('void', $statuses)) {
                    $builder->orWhereNotNull('voided_at');
                }
            });
        })->when($filters['grades'] ?? null, function (Builder $builder, $grades) {
            $builder->where(function (Builder $builder) use ($grades) {
                $gradeQuery = function (Builder $builder) use ($grades) {
                    $builder->whereIn('grade_level', $grades);
                };

                $builder->whereHas('student', $gradeQuery)
                    ->orWhereHas('students', $gradeQuery);
            });
        })->when($filters['date_start'] ?? null, function (Builder $builder, $date) {
            $parsed = Timezone::convertToLocal(Carbon::parse($date), 'Y-m-d');

            $builder->where('invoice_date', '>=', $parsed);
        })->when($filters['date_end'] ?? null, function (Builder $builder, $date) {
            $parsed = Timezone::convertToLocal(Carbon::parse($date), 'Y-m-d');

            $builder->where('invoice_date', '<=', $parsed);
        })->when($filters['due_start'] ?? null, function (Builder $builder, $date) {
            $builder->where('due_at', '>=', $date);
        })->when($filters['due_end'] ?? null, function (Builder $builder, $date) {
            $builder->where('invoice_date', '<=', $date);
        });

        $orderBy = $filters['orderBy'] ?? 'invoices.created_at';
        $orderDir = $filters['orderDir'] ?? 'desc';

        $builder->orderBy($orderBy, $orderDir);

        if ($orderBy !== 'title') {
            $builder->orderBy('invoices.title');
        }
    }

    public function scopeIsNotVoid(Builder $builder)
    {
        $builder->whereNull('voided_at');
    }

    public function scopeUnpaid(Builder $builder)
    {
        $builder->whereNull('paid_at');
    }

    public function scopePaid(Builder $builder)
    {
        $builder->whereNotNull('paid_at');
    }

    public function scopePaymentMade(Builder $builder)
    {
        $builder->where('invoices.amount_due', '!=', DB::raw('invoices.remaining_balance'));
    }

    public function scopeUnpublished(Builder $builder)
    {
        $builder->whereNull('published_at');
    }

    public function scopePublished(Builder $builder)
    {
        $builder->whereNotNull('published_at');
    }

    public function scopeNotAChild(Builder $builder)
    {
        $builder->whereNull('parent_uuid');
    }

    public function scopeBatch(Builder $builder, string $batch)
    {
        $builder->where('batch_id', $batch);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_uuid', 'uuid');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_uuid', 'uuid');
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

    public function invoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'invoice_uuid', 'uuid')
            ->orderBy('paid_at', 'desc');
    }

    public function invoiceTaxItems(): HasMany
    {
        return $this->hasMany(InvoiceTaxItem::class, 'invoice_uuid', 'uuid');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function invoicePaymentSchedule(): BelongsTo
    {
        return $this->belongsTo(InvoicePaymentSchedule::class, 'invoice_payment_schedule_uuid', 'uuid');
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            static::class,
            'parent_uuid',
            'uuid',
            'uuid',
            'student_uuid'
        )->distinct();
    }

    public function getIsVoidAttribute(): bool
    {
        return !!$this->voided_at;
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
        if (!$this->published_at) {
            return 'gray';
        }

        if ($this->paid_at) {
            return 'green';
        }

        if ($this->payment_made || !$this->available) {
            return 'yellow';
        }

        if ($this->past_due || $this->voided_at) {
            return 'red';
        }

        return 'yellow';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->parent_uuid) {
            return '';
        }

        if (!$this->published_at) {
            return __('Draft');
        }

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

    public function getIdAttribute($id): string
    {
        return $this->invoice_number;
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

    public function getStudentListAttribute(): string
    {
        if ($this->student_uuid && $this->relationLoaded('student')) {
            return $this->student->full_name;
        }

        if (!$this->student_uuid && $this->relationLoaded('students')) {
            return $this->students->pluck('full_name')->join(', ');
        }

        return '';
    }

    public static function getFullLoadRelationships(): array
    {
        return [
            'student',
            'students',
            'school',
            'currency',
            'invoiceItems.invoice.currency',
            'invoiceScholarships.invoice.currency',
            'invoicePaymentSchedule',
            'invoicePaymentSchedules',
            'invoicePaymentSchedules.invoicePaymentTerms',
            'invoicePayments.recordedBy',
            'invoicePayments.madeBy',
            'invoicePayments.currency',
            'children',
            'parent',
            'parent.invoicePaymentSchedules.invoicePaymentTerms',
        ];
    }

    public function fullLoad(): static
    {
        return $this->load(static::getFullLoadRelationships());
    }

    public function loadChildren(): static
    {
        if (!$this->is_parent) {
            return $this;
        }

        return $this->load([
            'children.currency',
            'children.student',
            'children.school',
            'children.invoiceItems.invoice.currency',
            'children.invoiceScholarships.invoice.currency',
            'children.invoicePaymentSchedules',
            'children.invoicePaymentSchedules.invoicePaymentTerms',
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
        $this->subtotal = $this->is_parent
            ? $this->children->sum('subtotal')
            : static::calculateSubtotalFromItems($this->invoiceItems);

        return $this;
    }

    public function setDiscountTotal(): static
    {
        if ($this->is_parent) {
            $this->discount_total = $this->children->sum('discount_total');

            return $this;
        }

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
        if ($this->is_parent) {
            $this->pre_tax_subtotal = $this->children->sum('pre_tax_subtotal');

            return $this;
        }

        $subtotal = $this->subtotal - $this->discount_total;

        $this->pre_tax_subtotal = max($subtotal, 0);

        return $this;
    }

    public function setTaxDue(): static
    {
        if ($this->is_parent) {
            $this->tax_due = $this->children->sum('tax_due');

            return $this;
        }

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
        $this->amount_due = $this->is_parent
            ? $this->children->sum('amount_due')
            : $this->pre_tax_subtotal + $this->tax_due;

        return $this;
    }

    public function setRemainingBalance(): static
    {
        $amountDue = $this->amount_due;

        // Calculate how much has already been paid in
        // and set the remaining_balance value based on that
        $this->total_paid = $this->getTotalPaid();

        // If the invoice has a schedule, use its amount
        // as the invoice's amount due, which may be different
        // from the invoice's base amount due (likely)
        if ($this->invoice_payment_schedule_uuid) {
            $amountDue = $this->invoicePaymentSchedule->amount;
        }

        $this->remaining_balance = $amountDue - $this->total_paid;
        $this->paid_at = $this->remaining_balance <= 0
            ? now()
            : null;

        return $this;
    }

    public function getTotalPaid(): int
    {
        $paymentsSum = $this->invoicePayments->sum('amount');

        // If this isn't the parent of a combined invoice
        // just return the sum of all the payments
        if (!$this->is_parent) {
            return $paymentsSum;
        }

        // Eager load any payments for the children
        $this->load('children.invoicePayments');

        // Add all the payments made to this invoice
        // and any child invoices since they could be
        // recorded independently of the parent
        return $paymentsSum +
            $this->children->reduce(function (int $total, Invoice $child) {
                return $total + $child->invoicePayments->sum('amount');
            }, 0);
    }

    public function setCalculatedAttributes(bool $save = false): static
    {
        // If this is a parent invoice, recalculate all the children
        // invoices before recalculating this one
        if ($this->is_parent) {
            $this->loadChildren()
                ->children->each(function (Invoice $invoice) use ($save) {
                    $invoice->setCalculatedAttributes($save);
                });
        }

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

    public function cacheCalculations(): static
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

        $this->setCalculatedAttributes()
            ->save();

        return $this;
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
            'invoicePaymentSchedules.invoicePaymentTerms',
            'invoiceTaxItems',
            'invoiceTaxItems.invoiceItem',
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

        // Tax attributes, some need converting
        $data['tax_rate'] = $this->tax_rate_converted;

        $taxItems = $this->invoiceTaxItems->keyBy('invoice_item_uuid');
        $data['tax_items'] = $this->invoiceItems
            ->map(fn (InvoiceItem $item) => [
                'item_id' => $item->uuid,
                'name' => $item->name,
                'selected' => $taxItems->has($item->uuid),
                'tax_rate' => $taxItems->has($item->uuid)
                    ? $taxItems->get($item->uuid)->tax_rate_converted
                    : $this->school->tax_rate_converted,
            ])
            ->toArray();

        return $data;
    }

    public function forEditing(bool $asBatch = false): array
    {
        $template = $this->asInvoiceTemplate();

        $template['uuid'] = $this->uuid;
        $template['batch_id'] = $this->batch_id;
        $template['students'] = $asBatch
            ? static::batch($this->batch_id)
                ->unpublished()
                ->pluck('student_uuid')
                ->toArray()
            : [$this->student_uuid];

        return $template;
    }

    public function convertToInvoiceTemplate(array $data): InvoiceTemplate
    {
        return InvoiceTemplate::create([
            'school_id' => $this->school_id,
            'user_uuid' => auth()->id(),
            'name' => $data['name'] ?? "Created from invoice {$this->number_formatted}",
            'template' => Arr::except($this->asInvoiceTemplate(), 'students'),
            'for_import' => false,
        ]);
    }

    /**
     * Sets the published date on the invoice
     *
     * @return $this
     */
    public function publish(): static
    {
        $this->update(['published_at' => now()]);

        return $this;
    }

    public static function successfullyCreatedResponse(Collection $results): RedirectResponse
    {
        if ($results->count() === 1) {
            session()->flash('success', __('Invoice created successfully.'));
        } else {
            session()->flash('success', __(':count invoices created successfully.', [
                'count' => $results->count(),
            ]));
        }

        $invoice = Invoice::where('uuid', $results->first())
            ->first();

        return redirect()->route('invoices.index', ['batch_id' => $invoice->batch_id]);
    }

    public static function successfullyUpdatedResponse(Collection $results): RedirectResponse
    {
        if ($results->count() === 1) {
            session()->flash('success', __('Invoice updated successfully.'));
        } else {
            session()->flash('success', __(':count invoices updated successfully.', [
                'count' => $results->count(),
            ]));
        }

        $invoice = Invoice::where('uuid', $results->first())
            ->first();

        return redirect()->route('invoices.index', ['batch_id' => $invoice->batch_id]);
    }

    public static function generateInvoiceNumber(string $prefix = ''): string
    {
        $id = (new Client(8))
            ->formattedId(static::ALPHABET);

        return $prefix . $id;
    }

    public function migrateActivity(string $uuid): static
    {
        $this->activities()
            ->update(['subject_id' => $uuid]);

        return $this;
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            "{$this->title}: {$this->invoice_number}",
            route('invoices.show', $this)
        );
    }

    public function recordPayment(InvoicePayment $payment, bool $log = true): static
    {
        // Some actions need to be applied to only the parent
        // so if this has a parent, use it. Otherwise,
        // just use the original invoice instance
        $parent = $this->parent_uuid ? $this->parent : $this;

        // If the payment associated a term
        // set the invoice's schedule to the term's
        if ($payment->invoice_payment_term_uuid) {
            $scheduleUuid = $payment->invoicePaymentTerm->invoice_payment_schedule_uuid;
        }

        if ($this->parent_uuid) {
            $this->setRemainingBalance()
                ->save();
        }

        // Only parent invoices should distribute to terms
        $parent->distributePaymentToTerms($payment)
            ->forceFill([
                'invoice_payment_schedule_uuid' => $scheduleUuid ?? $this->invoice_payment_schedule_uuid,
            ])
            ->setRemainingBalance()
            ->save();

        if ($log) {
            $this->logPayment($payment);
        }

        return $this;
    }

    public function distributePaymentsToTerms(bool $reset = false): static
    {
        // If we're resetting, set the remaining balance
        // of all the terms to the original amount due,
        // as if no payment had been made
        if ($reset) {
            $this->invoicePaymentTerms()
                ->update(['remaining_balance' => DB::raw('invoice_payment_terms.amount_due')]);
        }

        $this->load('invoicePayments');

        foreach ($this->invoicePayments as $payment) {
            $this->distributePaymentToTerms($payment, false);
        }

        // Save after all the payments processed
        $this->invoicePaymentSchedules
            ->each(function (InvoicePaymentSchedule $schedule) {
                $schedule->invoicePaymentTerms
                    ->each(function (InvoicePaymentTerm $term) {
                        $term->save();
                    });
            });

        return $this;
    }

    public function distributePaymentToTerms(InvoicePayment $payment, bool $save = true): static
    {
        // Load all the payment schedules to automatically distribute the
        // payment across all the terms
        if (!$this->relationLoaded('invoicePaymentSchedules')) {
            $this->load('invoicePaymentSchedules', 'invoicePaymentSchedules.invoicePaymentTerms');
        }

        // Use the payment and update all the terms' remaining balances
        // as if it was being applied to each payment schedule's terms
        foreach ($this->invoicePaymentSchedules as $schedule) {
            // Each schedule has its own amount due, since it's likely different
            // from the base amount due. We need to apply this payment to each
            // schedule and its terms
            $remainingSchedulePayment = $payment->amount;

            foreach ($schedule->invoicePaymentTerms as $term) {
                if ($term->remaining_balance === 0) {
                    continue;
                }

                // Stop iterating if there's no more payment
                // to distribute to the terms
                if ($remainingSchedulePayment <= 0) {
                    break;
                }

                $originalRemainingBalance = $term->remaining_balance;
                $remainingTermBalance = $term->remaining_balance - $remainingSchedulePayment;

                $term->fill([
                    'remaining_balance' => max($remainingTermBalance, 0),
                ]);

                if ($save) {
                    $term->save();
                }

                $remainingSchedulePayment -= abs($term->remaining_balance - $originalRemainingBalance);
            }
        }

        return $this;
    }

    public function logPayment(InvoicePayment $payment): static
    {
        // __(':user recorded a payment of :amount')
        // __(':user recorded a payment of :amount made by :made_by')
        $description = $payment->made_by
            ? ':user recorded a payment of :amount made by :made_by'
            : ':user recorded a payment of :amount';
        $payment->setRelation('currency', $this->currency);
        $properties = [
            'amount' => $payment->amount_formatted,
            'made_by' => optional($payment->madeBy)->full_name,
            'invoice_number' => $this->invoice_number,
        ];

        activity()
            ->on($this)
            ->withProperties($properties)
            ->log($description);

        // If this is a child invoice, log details to the parent invoice
        if ($this->parent_uuid) {
            // __(':user recorded a payment of :amount made to :invoice_number')
            // __(':user recorded a payment of :amount made to :invoice_number by :made_by')
            $description = $payment->made_by
                ? ':user recorded a payment of :amount made to :invoice_number by :made_by'
                : ':user recorded a payment of :amount made to :invoice_number';

            activity()
                ->on($this->parent)
                ->withProperties($properties)
                ->log($description);
        }

        return $this;
    }
}
