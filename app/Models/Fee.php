<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Brick\Money\Money;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Fee
 *
 * @mixin IdeHelperFee
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property string $name
 * @property string|null $code
 * @property string|null $description
 * @property int|null $amount
 * @property int|null $fee_category_id
 * @property int|null $department_id
 * @property int|null $course_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course|null $course
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\FeeCategory|null $feeCategory
 * @property-read mixed $amount_formatted
 * @property-read \App\Models\School $school
 * @property-read \App\Models\Tenant $tenant
 * @method static \Database\Factories\FeeFactory factory(...$parameters)
 * @method static Builder|Fee filter(array $filters)
 * @method static Builder|Fee newModelQuery()
 * @method static Builder|Fee newQuery()
 * @method static Builder|Fee query()
 * @method static Builder|Fee whereAmount($value)
 * @method static Builder|Fee whereCode($value)
 * @method static Builder|Fee whereCourseId($value)
 * @method static Builder|Fee whereCreatedAt($value)
 * @method static Builder|Fee whereDepartmentId($value)
 * @method static Builder|Fee whereDescription($value)
 * @method static Builder|Fee whereFeeCategoryId($value)
 * @method static Builder|Fee whereId($value)
 * @method static Builder|Fee whereName($value)
 * @method static Builder|Fee whereSchoolId($value)
 * @method static Builder|Fee whereTenantId($value)
 * @method static Builder|Fee whereUpdatedAt($value)
 */
class Fee extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToTenant;

    protected $guarded = [];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->select('fees.*');

        $builder->when($filters['s'] ?? null, function (Builder $builder, $search) {
            $builder->where(function (Builder $builder) use ($search) {
                $builder->where('fees.name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        });

        $orderBy = $filters['orderBy'] ?? 'name';
        $orderDir = $filters['orderDir'] ?? 'asc';

        $builder->when($orderBy === 'fee_categories.name', function (Builder $builder) {
            $builder->leftJoin('fee_categories', 'fees.fee_category_id', '=', 'fee_categories.id');
        })->when($orderBy === 'departments.name', function (Builder $builder) {
            $builder->leftJoin('departments', 'fees.department_id', '=', 'departments.id');
        });

        $builder->orderBy($orderBy, $orderDir);

        $builder->orderBy('fees.name', $orderDir);
    }

    public function getAmountFormattedAttribute()
    {
        return Money::ofMinor($this->amount, $this->school->currency->code)
            ->formatTo(auth()->user()->locale ?? 'en');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
