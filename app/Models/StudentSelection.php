<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
