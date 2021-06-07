<?php

namespace App\Models;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;

/**
 * @mixin IdeHelperInvoiceItem
 */
class InvoiceItem extends Model
{
    protected $fillable = [
        'uuid',
        'invoice_uuid',
        'batch_id',
        'fee_id',
        'name',
        'description',
        'amount_per_unit',
        'amount',
        'quantity',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_uuid', 'uuid');
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
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

    public function getAmountPerUnitFormattedAttribute()
    {
        if (
            !$this->relationLoaded('invoice') ||
            !$this->invoice->relationLoaded('school') ||
            !$this->invoice->school->relationLoaded('currency')
        ) {
            return null;
        }

        return Money::ofMinor($this->amount_per_unit, $this->invoice->school->currency->code)
            ->formatTo(optional(auth()->user())->locale ?? 'en');
    }

    public function calculateTotal(): int
    {
        return $this->amount_per_unit * $this->quantity;
    }
}
