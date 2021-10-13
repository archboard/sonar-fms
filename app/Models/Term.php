<?php

namespace App\Models;

use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTerm
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
        $start = substr((string) $this->start_year, 2);
        $end = substr((string) ($this->start_year + 1), 2);

        return $start . '-' . $end;
    }
}
