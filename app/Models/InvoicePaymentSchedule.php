<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperInvoicePaymentSchedule
 */
class InvoicePaymentSchedule extends Model
{
    use BelongsToInvoice;

    protected $fillable = [
        'uuid',
        'invoice_uuid',
        'batch_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'int',
    ];

    public function invoicePaymentTerms(): HasMany
    {
        return $this->hasMany(InvoicePaymentTerm::class, 'invoice_payment_schedule_uuid', 'uuid');
    }
}
