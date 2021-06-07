<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperStudentSelection
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
