<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCurrency
 */
class Currency extends Model
{
    protected $guarded = [];

    public function label(): Attribute
    {
        return Attribute::get(fn () => "{$this->currency} ({$this->code})");
    }
}
