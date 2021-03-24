<?php

namespace App\Models;

use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * @mixin IdeHelperStudent
 */
class Student extends Model
{
    use HasResource;
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'current_entry_date' => 'date',
        'current_exit_date' => 'date',
        'initial_district_entry_date' => 'date',
        'initial_school_entry_date' => 'date',
    ];

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
            $builder->where(function (Builder $builder) use ($search) {
                $builder->where(DB::raw("concat(first_name, ' ', last_name)"), 'ilike', "%{$search}%")
                    ->orWhere('student_number', 'ilike', "${search}%");
            });
        })->when($filters['grades'] ?? null, function (Builder $builder, $grades) {
            $builder->whereIn('grade_level', $grades);
        });

        // Enrollment status
        $enrolled = $filters['enrolled'] ?? true;

        if ($enrolled !== 'all') {
            $builder->where('enrolled', $enrolled);
        }

        $orderBy = $filters['orderBy'] ?? 'last_name';

        // Cast as int so postgres sorts correctly
        if ($orderBy === 'grade_level') {
            $orderBy = DB::raw('grade_level::int');
        }

        $builder->orderBy($orderBy, $filters['orderDir'] ?? 'asc');

        // Add the secondary first name order column
        $builder->orderBy('first_name', $filters['orderDir'] ?? 'asc');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class);
    }
}
