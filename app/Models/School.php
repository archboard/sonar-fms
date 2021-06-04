<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use GrantHolle\Http\Resources\Traits\HasResource;
use GrantHolle\PowerSchool\Api\Facades\PowerSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * App\Models\School
 *
 * @mixin IdeHelperSchool
 * @property int $id
 * @property int $tenant_id
 * @property int $sis_id
 * @property int|null $school_number
 * @property string $name
 * @property int|null $high_grade
 * @property int|null $low_grade
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $use_thousands_separator
 * @property bool $active
 * @property int|null $currency_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Course[] $courses
 * @property-read int|null $courses_count
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Fee[] $fees
 * @property-read int|null $fees_count
 * @property-read mixed $grade_levels
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Scholarship[] $scholarships
 * @property-read int|null $scholarships_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Section[] $sections
 * @property-read int|null $sections_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Student[] $students
 * @property-read int|null $students_count
 * @property-read \App\Models\Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Term[] $terms
 * @property-read int|null $terms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static Builder|School active()
 * @method static \Database\Factories\SchoolFactory factory(...$parameters)
 * @method static Builder|School newModelQuery()
 * @method static Builder|School newQuery()
 * @method static Builder|School query()
 * @method static Builder|School whereActive($value)
 * @method static Builder|School whereCreatedAt($value)
 * @method static Builder|School whereCurrencyId($value)
 * @method static Builder|School whereHighGrade($value)
 * @method static Builder|School whereId($value)
 * @method static Builder|School whereLowGrade($value)
 * @method static Builder|School whereName($value)
 * @method static Builder|School whereSchoolNumber($value)
 * @method static Builder|School whereSisId($value)
 * @method static Builder|School whereTenantId($value)
 * @method static Builder|School whereUpdatedAt($value)
 * @method static Builder|School whereUseThousandsSeparator($value)
 */
class School extends Model
{
    use HasFactory;
    use BelongsToTenant;
    use HasResource;

    protected $guarded = [];

    protected $casts = [
        'active_at' => 'datetime',
    ];

    public function scopeActive(Builder $builder)
    {
        $builder->where('active', true);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['staff_id']);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function scholarships(): HasMany
    {
        return $this->hasMany(Scholarship::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getGradeLevelsAttribute()
    {
        return range($this->low_grade, $this->high_grade);
    }

    public static function getFromPowerSchool(array $ids = []): Collection
    {
        $psSchools = PowerSchool::endpoint('/ws/v1/district/school')
            ->get();

        return collect($psSchools->schools->school);
    }

    public function syncFromPowerSchool(): static
    {
        $psSchool = PowerSchool::endpoint("/ws/v1/school/{$this->dcid}")
            ->get();

        $this->update([
            'name' => $psSchool->name,
            'sis_id' => $psSchool->id,
            'school_number' => $psSchool->school_number,
            'high_grade' => $psSchool->high_grade,
            'low_grade' => $psSchool->low_grade,
        ]);

        return $this;
    }

    public function syncDataFromSis()
    {
        $this->tenant->sisProvider()->fullSchoolSync($this);
    }
}
