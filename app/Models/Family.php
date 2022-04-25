<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperFamily
 */
class Family extends Model
{
    use HasFactory;
    use HasResource;
    use BelongsToSchool;

    protected $guarded = [];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
