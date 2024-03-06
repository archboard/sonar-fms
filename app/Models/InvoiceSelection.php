<?php

namespace App\Models;

use App\Traits\ScopeToCurrentSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperInvoiceSelection
 */
class InvoiceSelection extends Model
{
    use ScopeToCurrentSchool;

    protected $guarded = [];

    public $timestamps = false;

    public function scopeInvoice(Builder $builder, string $uuid)
    {
        $builder->where('invoice_uuid', $uuid);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
