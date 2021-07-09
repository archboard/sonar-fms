<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToUser;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperInvoiceTemplate
 */
class InvoiceTemplate extends Model
{
    use BelongsToSchool;
    use BelongsToUser;
    use HasResource;
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'template' => 'json',
        'for_import' => 'boolean',
    ];
}
