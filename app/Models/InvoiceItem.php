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
 * App\Models\InvoiceItem
 *
 * @mixin IdeHelperInvoiceItem
 * @property int $id
 * @property string $uuid
 * @property string $invoice_uuid
 * @property string|null $batch_id
 * @property int|null $fee_id
 * @property bool $sync_with_fee
 * @property string|null $name
 * @property string|null $description
 * @property int|null $amount_per_unit
 * @property int|null $amount
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fee|null $fee
 * @property-read mixed $amount_formatted
 * @property-read mixed $amount_per_unit_formatted
 * @property-read \App\Models\Invoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereAmountPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereFeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereInvoiceUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereSyncWithFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InvoiceItem whereUuid($value)
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
