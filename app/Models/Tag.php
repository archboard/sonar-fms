<?php

namespace App\Models;

use Spatie\Tags\Tag as Model;

class Tag extends Model
{
    public static function student(School $school): string
    {
        return "students-{$school->id}";
    }
}
