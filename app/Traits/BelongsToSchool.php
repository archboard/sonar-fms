<?php

namespace App\Traits;

use App\Models\Currency;
use App\Models\School;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

trait BelongsToSchool
{
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function currency(): HasOneThrough
    {
        return $this->hasOneThrough(
            Currency::class,
            School::class,
            'id',
            'id',
            'school_id',
            'currency_id'
        );
    }
}
