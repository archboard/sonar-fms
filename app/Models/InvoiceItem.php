<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use Brick\Money\Money;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperInvoiceItem
 */
class InvoiceItem extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToInvoice;

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

    public function getIncrementing(): bool
    {
        return false;
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function getAmountFormattedAttribute()
    {
        if (
            !$this->relationLoaded('invoice') ||
            !$this->invoice->relationLoaded('currency')
        ) {
            return null;
        }

        return displayCurrency($this->amount, $this->invoice->currency);
    }

    public function getAmountPerUnitFormattedAttribute()
    {
        if (
            !$this->relationLoaded('invoice') ||
            !$this->invoice->relationLoaded('currency')
        ) {
            return null;
        }

        return displayCurrency($this->amount_per_unit, $this->invoice->currency);
    }

    public function setAmount(): static
    {
        $this->amount = $this->calculateTotal();

        return $this;
    }

    public function calculateTotal(): int
    {
        return $this->amount_per_unit * $this->quantity;
    }
}
