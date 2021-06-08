<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoicePaymentSchedule extends Model
{
    use BelongsToTenant;

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
