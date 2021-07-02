<?php

namespace App\Traits;

use App\Utilities\NumberUtility;

trait HasPercentageAttribute
{
    public function setPercentageAttribute($value)
    {
        $this->attributes['percentage'] = NumberUtility::convertPercentageFromUser($value);
    }

    public function getPercentageAttribute($value)
    {
        return (float) $value ?? 0;
    }

    public function getPercentageFormattedAttribute()
    {
        return "{$this->percentage_converted}%";
    }

    public function getPercentageConvertedAttribute()
    {
        $value = $this->percentage * 100;

        return (string) $value;
    }
}
