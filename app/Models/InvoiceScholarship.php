<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @mixin IdeHelperInvoiceScholarship
 */
class InvoiceScholarship extends Model
{
    protected $fillable = [
        'invoice_uuid',
        'scholarship_id',
        'sync_with_scholarship',
        'name',
        'percentage',
        'amount',
        'resolution_strategy',
        'calculated_amount',
    ];

    protected $casts = [
        'amount' => 'integer',
        'calculated_amount' => 'integer',
        'percentage' => 'float',
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
        return $value ?? 0;
    }

    public function calculateAmount(int $invoiceTotal): int
    {
        $discount = $this->amount;
        $percentageDiscount = (int) round($invoiceTotal * ($this->percentage / 100));

        if ($discount > 0 && $percentageDiscount > 0) {
            $strategy = $this->resolution_strategy;
            $resolver = new $strategy();

            return $resolver($discount, $percentageDiscount);
        }

        return $discount > 0
            ? $discount
            : $percentageDiscount;
    }

    public static function generateAttributesForInsert(string $invoiceUuid, array $item, int $invoiceTotal, Collection $scholarships): array
    {
        unset($item['id']);
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
        $item['calculated_amount'] = (new static($item))->calculateAmount($invoiceTotal);

        return Arr::only($item, static::make()->fillable);
    }
}
