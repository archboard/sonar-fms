<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StudentSelection
 *
 * @mixin IdeHelperStudentSelection
 * @property int $school_id
 * @property int $student_id
 * @property int $user_id
 * @method static Builder|StudentSelection newModelQuery()
 * @method static Builder|StudentSelection newQuery()
 * @method static Builder|StudentSelection query()
 * @method static Builder|StudentSelection student($studentId)
 * @method static Builder|StudentSelection whereSchoolId($value)
 * @method static Builder|StudentSelection whereStudentId($value)
 * @method static Builder|StudentSelection whereUserId($value)
 */
class StudentSelection extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function scopeStudent(Builder $builder, $studentId)
    {
        $builder->where('student_id', $studentId);
    }
}
