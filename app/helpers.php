<?php

use App\Models\Currency;
use Brick\Money\Money;
use Illuminate\Support\Collection;

if (!function_exists('displayCurrency')) {
    /**
     * Displays an integer as a formatted currency
     * based on the given currency and user's locale
     *
     * @param int|null $amount
     * @param Currency|null $currency
     * @return string
     */
    function displayCurrency(int $amount = null, Currency $currency = null): string {
        if (is_null($currency)) {
            $currency = request()->school()->currency ??
                new Currency(['code' => 'USD']);
        }

        try {
            return Money::ofMinor($amount ?? 0, $currency->code)
                ->formatTo(optional(auth()->user())->locale ?? 'en');
        } catch (\Brick\Money\Exception\UnknownCurrencyException $exception) {
            return (string) $amount;
        }
    }
}

if (!function_exists('timezones')) {
    /**
     * Gets a list of all timezones
     * or the formatted name of the given timezone
     *
     * @param string|null $timezone
     * @return Collection|string
     */
    function timezones(string $timezone = null): Collection|string {
        $zones = collect(DateTimeZone::listIdentifiers())
            ->mapWithKeys(function ($zoneId) {
                $zone = IntlTimeZone::createTimeZone($zoneId);
                $name = $zone->getDisplayName($zone->useDaylightTime());

                return [
                    $zoneId => "{$zoneId} ({$name})",
                ];
            });

        if (is_null($timezone)) {
            return $zones;
        }

        return $zones->get($timezone);
    }
}
