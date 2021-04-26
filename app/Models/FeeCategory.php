<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperFeeCategory
 */
class FeeCategory extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToTenant;

    protected $guarded = [];
}
