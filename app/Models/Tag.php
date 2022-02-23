<?php

namespace App\Models;

use GrantHolle\Http\Resources\Traits\HasResource;
use Spatie\Tags\Tag as Model;

/**
 * @mixin IdeHelperTag
 */
class Tag extends Model
{
    use HasResource;

    public static function student(School $school): string
    {
        return "students-{$school->id}";
    }
}
