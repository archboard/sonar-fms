<?php

namespace App\Traits;

use Brick\Money\Money;

trait HasAmountAttribute
{
    public function getAmountFormattedAttribute(): string
    {
        if ($this->relationLoaded('currency')) {
            return Money::ofMinor($this->amount, $this->currency->code)
                ->formatTo(auth()->user()->locale ?? 'en');
        }

        return '';
    }
}
