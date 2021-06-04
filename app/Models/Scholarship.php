<?php

namespace App\Models;

use App\ResolutionStrategies\Greatest;
use App\ResolutionStrategies\Least;
use App\Traits\BelongsToSchool;
use App\Traits\BelongsToTenant;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Scholarship
 *
 * @mixin IdeHelperScholarship
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property string $name
 * @property string|null $description
 * @property float|null $percentage
 * @property int|null $amount
 * @property string|null $resolution_strategy
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $percentage_formatted
 * @property-read \App\Models\School $school
 * @property-read \App\Models\Tenant $tenant
 * @method static \Database\Factories\ScholarshipFactory factory(...$parameters)
 * @method static Builder|Scholarship filter(array $filters)
 * @method static Builder|Scholarship newModelQuery()
 * @method static Builder|Scholarship newQuery()
 * @method static Builder|Scholarship query()
 * @method static Builder|Scholarship whereAmount($value)
 * @method static Builder|Scholarship whereCreatedAt($value)
 * @method static Builder|Scholarship whereDescription($value)
 * @method static Builder|Scholarship whereId($value)
 * @method static Builder|Scholarship whereName($value)
 * @method static Builder|Scholarship wherePercentage($value)
 * @method static Builder|Scholarship whereResolutionStrategy($value)
 * @method static Builder|Scholarship whereSchoolId($value)
 * @method static Builder|Scholarship whereTenantId($value)
 * @method static Builder|Scholarship whereUpdatedAt($value)
 */
class Scholarship extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToTenant;
    use BelongsToSchool;

    protected $guarded = [];

    protected $casts = [
        'percentage' => 'float',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, $search) {
            $builder->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        });

        $orderBy = $filters['orderBy'] ?? 'name';
        $orderDir = $filters['orderDir'] ?? 'asc';

        $builder->orderBy($orderBy, $orderDir);
        $builder->orderBy('scholarships.name', $orderDir);
    }

    public function getPercentageFormattedAttribute()
    {
        return $this->percentage . '%';
    }

    public static function getResolutionStrategies(): array
    {
        return [
            Least::class => __('Least'),
            Greatest::class => __('Greatest'),
        ];
    }
}
