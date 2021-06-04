<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Section
 *
 * @mixin IdeHelperSection
 * @property int $id
 * @property int $tenant_id
 * @property int $school_id
 * @property int|null $term_id
 * @property int $course_id
 * @property int $user_id
 * @property int $sis_id
 * @property string|null $section_number
 * @property string|null $expression
 * @property string|null $external_expression
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Student[] $students
 * @property-read int|null $students_count
 * @method static \Illuminate\Database\Eloquent\Builder|Section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Section query()
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereExpression($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereExternalExpression($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereSchoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereSectionNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereSisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereUserId($value)
 */
class Section extends Model
{
    protected $guarded = [];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class);
    }
}
