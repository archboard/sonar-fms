<?php

namespace App\Traits;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToInvoice
{
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_uuid', 'uuid');
    }
}
