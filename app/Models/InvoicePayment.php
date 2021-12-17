<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\HasAmountAttribute;
use App\Traits\ScopeToCurrentSchool;
use App\Traits\UsesUuid;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use JamesMills\LaravelTimezone\Facades\Timezone;

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
    use UsesUuid;

    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'int',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->select('invoice_payments.*');

        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
            $builder->whereHas('invoice', function (Builder $builder) use ($search) {
                $builder->search($search);
            });
        });

        $builder->join('invoices', 'invoice_payments.invoice_uuid', '=', 'invoices.uuid');
        $orderDir = $filters['orderDir'] ?? 'desc';
        $orderBy = $filters['orderBy'] ?? 'paid_at';

        $builder->orderBy($orderBy, $orderDir)
            ->orderBy('invoice_payments.created_at', $orderDir);
    }

    public function getPaidAtFormattedAttribute(): string
    {
        if (!$this->paid_at) {
            return '';
        }

        return Timezone::convertToLocal($this->paid_at, 'M j, Y');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_uuid', 'uuid');
    }

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
        return $this->belongsTo(User::class, 'recorded_by', 'uuid');
    }

    public function madeBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'made_by', 'uuid');
    }
}
