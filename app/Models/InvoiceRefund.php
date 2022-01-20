<?php

namespace App\Models;

use App\Traits\BelongsToInvoice;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;
use App\Traits\HasAmountAttribute;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JamesMills\LaravelTimezone\Facades\Timezone;

/**
 * @mixin IdeHelperInvoiceRefund
 */
class InvoiceRefund extends Model
{
    use HasFactory;
    use BelongsToTenant;
    use BelongsToSchool;
    use BelongsToInvoice;
    use BelongsToUser;
    use HasAmountAttribute;
    use HasResource;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'refunded_at' => 'datetime',
    ];

    public function getRefundedAtFormattedAttribute(): string
    {
        if (!$this->refunded_at) {
            return '';
        }

        return Timezone::convertToLocal($this->refunded_at, 'M j, Y');
    }
}
