<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperInvoiceTaxItem
 */
class InvoiceTaxItem extends Model
{
    use HasResource;
    use BelongsToInvoice;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'int',
    ];

    public function getAmountAttribute($value): int
    {
        return (int) ($value ?? 0);
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

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(
            InvoiceItem::class,
            'invoice_item_uuid',
            'uuid'
        );
    }
}
