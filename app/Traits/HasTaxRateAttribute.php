<?php

namespace App\Traits;

use App\Utilities\NumberUtility;

trait HasTaxRateAttribute
{
    public function setTaxRateAttribute($value)
    {
        $this->attributes['tax_rate'] = NumberUtility::convertPercentageFromUser($value);
    }

    public function getTaxRateAttribute($value)
    {
        return (float) $value ?: 0;
    }

    public function getTaxRateFormattedAttribute()
    {
        return $this->tax_rate_converted . '%';
    }

    public function getTaxRateConvertedAttribute()
    {
        $value = $this->tax_rate * 100;

        return (string) $value;
    }
}
