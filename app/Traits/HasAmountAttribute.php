<?php

namespace App\Traits;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasAmountAttribute
{
    public function amountFormatted(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->relationLoaded('currency')) {
                return Money::ofMinor($this->amount, $this->currency->code)
                    ->formatTo(auth()->user()?->locale ?? 'en');
            }

            return '';
        });
    }
}
