<?php

namespace App\Models;

use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Term
 *
 * @mixin IdeHelperTerm
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property int $sis_id
 * @property int $sis_assigned_id
 * @property string $name
 * @property string $abbreviation
 * @property int $start_year
 * @property int $portion
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $is_current
 * @property-read mixed $school_years
 * @method static \Database\Factories\TermFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Term newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Term newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Term query()
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term wherePortion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereSisAssignedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereSisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereStartYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereUpdatedAt($value)
 */
class Term extends Model
{
    use HasFactory;
    use HasResource;

    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function getIsCurrentAttribute()
    {
        $today = today();

        return $this->starts_at <= $today &&
            $this->ends_at >= $today;
    }

    public function getSchoolYearsAttribute()
    {
        $start = substr($this->start_year, 2);
        $end = substr($this->start_year + 1, 2);

        return $start . '-' . $end;
    }
}
