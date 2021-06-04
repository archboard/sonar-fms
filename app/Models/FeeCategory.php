<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FeeCategory
 *
 * @mixin IdeHelperFeeCategory
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Tenant $tenant
 * @method static \Database\Factories\FeeCategoryFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeeCategory whereUpdatedAt($value)
 */
class FeeCategory extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToTenant;

    protected $guarded = [];
}
