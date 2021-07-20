<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperInvoicePaymentTerm
 */
class InvoicePaymentTerm extends Model
{
    use BelongsToInvoice;
    use HasResource;

    protected $fillable = [
        'uuid',
        'invoice_uuid',
        'invoice_payment_schedule_uuid',
        'batch_id',
        'amount',
        'amount_due',
        'remaining_balance',
        'due_at',
        'notified_at',
        'notify',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'notify_at' => 'datetime',
        'amount' => 'int',
        'notify' => 'boolean',
    ];

    public function invoicePaymentSchedule(): BelongsTo
    {
        return $this->belongsTo(InvoicePaymentSchedule::class, 'invoice_payment_schedule_uuid', 'uuid');
    }
}
