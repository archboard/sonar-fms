<?php

use App\Models\Currency;
use Brick\Money\Money;

if (!function_exists('displayCurrency')) {
    function displayCurrency(int $amount, Currency $currency = null): string {
        if (is_null($currency)) {
            $currency = request()->school()->currency;
        }

        return Money::ofMinor($amount, $currency->code)
            ->formatTo(optional(auth()->user())->locale ?? 'en');
    }
}
