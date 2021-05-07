<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * @mixin IdeHelperInvoiceItem
 */
class InvoiceItem extends Model
{
    protected $guarded = [];

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
     * @param Collection $items The collection of items from CreateNewInvoiceRequest
     * @param Collection $fees The fees should be keyed by the id
     * @return array
     */
    public static function generateAttributesForInsert(string $invoiceUuid, Collection $items, Collection $fees): array
    {
        return $items->map(function ($item) use ($invoiceUuid, $fees) {
            unset($item['id']);
            $item['invoice_uuid'] = $invoiceUuid;

            if ($item['sync_with_fee']) {
                $fee = $fees->get($item['fee_id']);

                $item['name'] = $fee->name;
                $item['amount_per_unit'] = $fee->amount;
            }

            return $item;
        })->toArray();
    }
}
