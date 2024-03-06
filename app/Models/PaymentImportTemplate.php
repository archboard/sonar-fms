<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToUser;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPaymentImportTemplate
 */
class PaymentImportTemplate extends Model
{
    use BelongsToSchool;
    use BelongsToUser;
    use HasFactory;
    use HasResource;

    protected $guarded = [];

    protected $casts = [
        'template' => 'json',
    ];
}
