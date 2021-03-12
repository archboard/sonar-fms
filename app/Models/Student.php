<?php

namespace App\Models;

use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperStudent
 */
class Student extends Model
{
    use HasFactory;
    use HasResource;

    protected $guarded = [];
}
