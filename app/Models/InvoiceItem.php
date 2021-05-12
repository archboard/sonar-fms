<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @mixin IdeHelperInvoiceItem
 */
class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_uuid',
        'sync_with_fee',
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

    /**
     * @param string $invoiceUuid
     * @param array $item The item received from CreateNewInvoiceRequest
     * @param Collection $fees The fees should be keyed by the id
     * @return array
     */
    public static function generateAttributesForInsert(string $invoiceUuid, array $item, Collection $fees): array
    {
        unset($item['id']);
        $item['invoice_uuid'] = $invoiceUuid;

        if ($item['sync_with_fee'] && $fee = $fees->get($item['fee_id'])) {
            $item['name'] = $fee->name;
            $item['amount_per_unit'] = $fee->amount;
        }

        // Cache the total line item
        $item['amount'] = $item['amount_per_unit'] * $item['quantity'];

        return Arr::only($item, static::make()->fillable);
    }
}
