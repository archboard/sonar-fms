<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Silber\Bouncer\Database\HasRolesAndAbilities;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasResource;
    use HasRolesAndAbilities;
    use BelongsToTenant;

    const TEACHER = 'teacher';
    const DISTRICT_ADMIN = 'district admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Gets the users who have an ability directly or through a role
     *
     * @param Builder $query
     * @param string $ability
     */
    public function scopeWhereCan(Builder $query, string $ability)
    {
        $query->where(function ($query) use ($ability) {
            // direct
            $query->whereHas('abilities', function ($query) use ($ability) {
                $query->byName($ability);
            });
            // through roles
            $query->orWhereHas('roles', function ($query) use ($ability) {
                 $query->whereHas('abilities', function ($query) use ($ability) {
                     $query->byName($ability);
                 });
             });
         });
    }

    public function getSchoolPermissionsAttribute(): array
    {
        return $this->getPermissionsForSchool();
    }

    public function getStudentSelectionAttribute()
    {
        if ($this->relationLoaded('studentSelections')) {
            return $this->studentSelections->map->student_id;
        }

        return collect();
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class)
            ->withPivot(['staff_id']);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class);
    }

    public function studentSelections(): HasMany
    {
        return $this->hasMany(StudentSelection::class)
            ->join('users', function (JoinClause $join) {
                $join->on('student_selections.school_id', '=', 'users.school_id');
            });
    }

    public function getPermissionsForSchool(School $school = null): array
    {
        $school = $school ?? $this->school;

        return [
            [
                'label' => __('Change school settings'),
                'permission' => 'change settings',
                'selected' => $this->can('change settings', $school),
            ],
        ];
    }

    /**
     * Adds a student to a user's selection
     *
     * @param int|Student $studentId
     * @return User
     */
    public function selectStudent(Student|int $studentId): User
    {
        if ($studentId instanceof Student) {
            $studentId = $studentId->id;
        }

        $exists = $this->studentSelections()
            ->student($studentId)
            ->exists();

        if (!$exists) {
            DB::table('student_selections')->insert([
                'school_id' => $this->school_id,
                'student_id' => $studentId,
                'user_id' => $this->id,
            ]);
        }

        return $this;
    }
}
