<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperSection
 */
class Section extends Model
{
    protected $guarded = [];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class);
    }
}
