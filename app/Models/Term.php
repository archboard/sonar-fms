<?php

namespace App\Models;

use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTerm
 */
class Term extends Model
{
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
        $start = substr($this->start_year, 2);
        $end = substr($this->start_year + 1, 2);

        return $start . '-' . $end;
    }
}
