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

    public function scopeFilter(Builder $builder, array $filters)
    {
        $builder->when($filters['s'] ?? null, function (Builder $builder, string $search) {
            $builder->where(function (Builder $builder) use ($search) {
                $builder->where(DB::raw("concat(first_name, ' ', last_name)"), 'ilike', "%{$search}%")
                    ->orWhere('student_number', 'ilike', "${search}%");
            });
        })->when(true, function (Builder $builder) {
            $builder->where(function (Builder $builder) {
                $builder->where('enrolled', true);
            });
        });

        $builder->orderBy($filters['orderBy'] ?? 'last_name', $filters['orderDir'] ?? 'asc');
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
