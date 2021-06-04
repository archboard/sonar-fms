<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SyncTime
 *
 * @mixin IdeHelperSyncTime
 * @property int $id
 * @property int $tenant_id
 * @property int $hour
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant $tenant
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime query()
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncTime whereUpdatedAt($value)
 */
class SyncTime extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'hour' => 'integer',
    ];
}
