<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperInvoicePdf
 */
class InvoicePdf extends Model
{
    use BelongsToSchool;
    use BelongsToTenant;
    use BelongsToUser;

    protected $guarded = [];
}
