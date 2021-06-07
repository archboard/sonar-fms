<?php

namespace App\Models;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

/**
 * App\Models\InvoiceScholarship
 *
 * @mixin IdeHelperInvoiceScholarship
 * @property int $id
 * @property string $uuid
 * @property string $invoice_uuid
 * @property string|null $batch_id
 * @property int|null $scholarship_id
 * @property bool $sync_with_scholarship
 * @property string $name
 * @property string|null $percentage
 * @property int|null $amount
 * @property string|null $resolution_strategy
 * @property int|null $calculated_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceItem[] $appliesTo
 * @property-read int|null $applies_to_count
 * @property-read mixed $amount_formatted
 * @property-read mixed $calculated_amount_formatted
 * @property-read mixed $percentage_decimal
 * @property-read mixed $percentage_formatted
 * @property-read \App\Models\Invoice $invoice
 * @property-read \App\Models\Scholarship|null $scholarship
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereCalculatedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereInvoiceUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereResolutionStrategy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereScholarshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereSyncWithScholarship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceScholarship whereUuid($value)
 */
class InvoiceScholarship extends Model
{
    protected $fillable = [
        'uuid',
        'invoice_uuid',
        'batch_id',
        'scholarship_id',
        'name',
        'percentage',
        'amount',
        'resolution_strategy',
        'calculated_amount',
    ];

    protected $casts = [
        'amount' => 'integer',
        'calculated_amount' => 'integer',
        'sync_with_scholarship' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_uuid', 'uuid');
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function appliesTo(): BelongsToMany
    {
        return $this->belongsToMany(
            InvoiceItem::class,
            'invoice_item_invoice_scholarship',
            'invoice_scholarship_uuid',
            'invoice_item_uuid',
            'uuid',
            'uuid'
        );
    }

    public function getPercentageFormattedAttribute()
    {
        return $this->percentage . '%';
    }

    public function getAmountAttribute($value)
    {
        return $value ?? 0;
    }

    public function getPercentageAttribute($value)
    {
        return (float) $value ?? 0;
    }

    public function getPercentageDecimalAttribute()
    {
        return $this->percentage / 100;
    }

    public function getAmountFormattedAttribute()
    {
        if (
            !$this->relationLoaded('invoice') ||
            !$this->invoice->relationLoaded('school') ||
            !$this->invoice->school->relationLoaded('currency')
        ) {
            return null;
        }

        return Money::ofMinor($this->amount, $this->invoice->school->currency->code)
            ->formatTo(optional(auth()->user())->locale ?? 'en');
    }

    public function getCalculatedAmountFormattedAttribute()
    {
        if (
            !$this->relationLoaded('invoice') ||
            !$this->invoice->relationLoaded('school') ||
            !$this->invoice->school->relationLoaded('currency')
        ) {
            return null;
        }

        return Money::ofMinor($this->calculated_amount, $this->invoice->school->currency->code)
            ->formatTo(optional(auth()->user())->locale ?? 'en');
    }

    public function getApplicableSubtotal(): int
    {
        // When it's not applied to the
        if ($this->appliesTo->isEmpty()) {
            return $this->invoice->calculateSubtotal();
        }

        return Invoice::calculateSubtotalFromItems($this->appliesTo);
    }

    public function calculateAmount(): int
    {
        $subtotal = $this->getApplicableSubtotal();
        $discount = $this->amount;
        $percentageDiscount = (int) round($subtotal * ($this->percentage / 100));

        if ($discount > 0 && $percentageDiscount > 0) {
            $strategy = $this->resolution_strategy;
            $resolver = new $strategy();

            return $resolver($discount, $percentageDiscount);
        }

        return $discount > 0
            ? $discount
            : $percentageDiscount;
    }

    public static function generateAttributesForInsert(string $invoiceUuid, array $item, Collection $scholarships): array
    {
        $item['uuid'] = Uuid::uuid4();
        $item['invoice_uuid'] = $invoiceUuid;

        if (
            $item['sync_with_scholarship'] &&
            $scholarship = $scholarships->get($item['scholarship_id'])
        ) {
            $item['name'] = $scholarship->name;
            $item['amount'] = $scholarship->amount;
            $item['percentage'] = $scholarship->percentage;
            $item['resolution_strategy'] = $scholarship->resolution_strategy;
        }

        // Cache the total line item
        // Need to know which line items this applies to
        $item['calculated_amount'] = (new static($item))->calculateAmount();

        return Arr::only($item, (new static)->fillable);
    }
}
