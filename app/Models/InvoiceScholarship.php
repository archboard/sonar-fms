<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use App\Traits\HasPercentageAttribute;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasFactory;
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

    // These are the attributes/properties that are
    // used on the invoice form based on the API Resource
    public static array $formAttributes = [
        'id',
        'scholarship_id',
        'name',
        'amount',
        'percentage',
        'resolution_strategy',
        'applies_to',
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

    public function getAmountAttribute($value): int
    {
        return $value ?? 0;
    }

    public function getPercentageDecimalAttribute(): float
    {
        return $this->percentage / 100;
    }

    public function getAmountFormattedAttribute(): ?string
    {
        if (
            !$this->relationLoaded('invoice') ||
            !$this->invoice->relationLoaded('currency')
        ) {
            return null;
        }

        return displayCurrency($this->amount, $this->invoice->currency);
    }

    public function getCalculatedAmountFormattedAttribute(): ?string
    {
        if (
            !$this->relationLoaded('invoice') ||
            !$this->invoice->relationLoaded('currency')
        ) {
            return null;
        }

        return displayCurrency($this->calculated_amount, $this->invoice->currency);
    }

    public function getApplicableSubtotal(): int
    {
        $items = $this->appliesTo->isEmpty()
            ? $this->invoice->invoiceItems
            : $this->appliesTo;

        return Invoice::calculateSubtotalFromItems($items);
    }

    public function setAmount(): self
    {
        $this->amount = $this->calculateAmount();

        return $this;
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
}
