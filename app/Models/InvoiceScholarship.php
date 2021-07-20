<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use App\Traits\HasPercentageAttribute;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

/**
 * @mixin IdeHelperInvoiceScholarship
 */
class InvoiceScholarship extends Model
{
    use BelongsToInvoice;
    use HasPercentageAttribute;

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

    public function getIncrementing()
    {
        return false;
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

    public function getAmountAttribute($value)
    {
        return $value ?? 0;
    }

    public function getPercentageDecimalAttribute()
    {
        return $this->percentage / 100;
    }

    public function getAmountFormattedAttribute()
    {
        if (!$this->invoice->relationLoaded('currency')) {
            return null;
        }

        return displayCurrency($this->amount, $this->invoice->currency);
    }

    public function getCalculatedAmountFormattedAttribute()
    {
        if (!$this->invoice->relationLoaded('currency')) {
            return null;
        }

        return displayCurrency($this->calculated_amount, $this->invoice->currency);
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
        $percentageDiscount = (int) round($subtotal * $this->percentage);

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
