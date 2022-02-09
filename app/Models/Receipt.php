<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @mixin IdeHelperReceipt
 */
class Receipt extends Model
{
    use HasFactory;
    use BelongsToUser;
    use BelongsToSchool;

    protected $guarded = [];

    public function invoicePayment(): BelongsTo
    {
        return $this->belongsTo(InvoicePayment::class);
    }

    public function invoice(): HasOneThrough
    {
        return $this->hasOneThrough(
            Invoice::class,
            InvoicePayment::class,
            'uuid',
            'uuid',
            'invoice_payment_uuid',
            'invoice_uuid'
        );
    }
}
