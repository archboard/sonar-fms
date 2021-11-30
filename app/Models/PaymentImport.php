<?php

namespace App\Models;

use App\Concerns\FileImport;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;
use App\Traits\ImportsFiles;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @mixin IdeHelperPaymentImport
 */
class PaymentImport extends Model implements FileImport
{
    use BelongsToTenant;
    use BelongsToSchool;
    use BelongsToUser;
    use ImportsFiles;
    use HasResource;

    protected $guarded = [];

    protected $casts = [
        'mapping' => 'json',
        'results' => 'json',
        'total_records' => 'int',
        'imported_records' => 'int',
        'failed_records' => 'int',
        'heading_row' => 'int',
        'starting_row' => 'int',
        'imported_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
            $builder->where('file_path', 'ilike', "/%{$search}%");
        });
    }

    public function invoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Invoice::class,
            InvoicePayment::class,
            'payment_import_id',
            'uuid',
            'id',
            'invoice_uuid',
        )->distinct();
    }

    public function getMappingValidator(): \Illuminate\Validation\Validator
    {
        // TODO: Implement getMappingValidator() method.
    }

    public function rollBack(): static
    {
        $invoices = $this->invoices()->pluck('uuid');

        $this->invoicePayments()->delete();

        // Run jobs on the invoices to recalculate balances

        return $this->reset();
    }
}
