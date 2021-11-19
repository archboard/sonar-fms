<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperInvoicePaymentSchedule
 */
class InvoicePaymentSchedule extends Model
{
    use HasFactory;
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

    // These are the attributes/properties that are
    // used on the invoice form based on the API Resource
    public static array $formAttributes = [
        'id',
        'terms',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public function invoicePaymentTerms(): HasMany
    {
        return $this->hasMany(InvoicePaymentTerm::class, 'invoice_payment_schedule_uuid', 'uuid')
            ->orderBy('due_at');
    }

    public function setAmount(): static
    {
        $this->amount = $this->invoicePaymentTerms
            ->reduce(
                fn (int $total, InvoicePaymentTerm $term) => $total + $term->amount, 0
            );

        return $this;
    }
}
