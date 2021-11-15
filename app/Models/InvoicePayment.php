<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\HasAmountAttribute;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @mixin IdeHelperInvoicePayment
 */
class InvoicePayment extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToTenant;
    use BelongsToSchool;
    use BelongsToInvoice;
    use HasAmountAttribute;

    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'int',
    ];

    public function invoicePaymentTerm(): BelongsTo
    {
        return $this->belongsTo(
            InvoicePaymentTerm::class,
            'invoice_payment_term_uuid',
            'uuid'
        );
    }

    public function invoicePaymentSchedule(): HasOneThrough
    {
        return $this->hasOneThrough(
            InvoicePaymentSchedule::class,
            InvoicePaymentTerm::class,
            'uuid',
            'uuid',
            'invoice_payment_term_uuid',
            'invoice_payment_schedule_uuid'
        );
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by', 'id');
    }

    public function madeBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'made_by', 'id');
    }
}
