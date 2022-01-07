<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperInvoicePdf
 */
class InvoicePdf extends Model
{
    use HasFactory;
    use BelongsToTenant;
    use BelongsToSchool;
    use BelongsToUser;

    protected $guarded = [];
}
