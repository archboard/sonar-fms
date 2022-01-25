<?php

namespace App\Traits;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToInvoice
{
    public function scopeForInvoice(Builder $builder, string $invoiceUuid)
    {
        $builder->where('invoice_uuid', $invoiceUuid);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_uuid', 'uuid');
    }
}
