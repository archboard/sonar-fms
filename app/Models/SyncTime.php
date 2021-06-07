<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSyncTime
 */
class SyncTime extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'hour' => 'integer',
    ];
}
