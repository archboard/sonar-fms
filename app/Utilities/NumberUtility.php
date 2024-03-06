<?php

namespace App\Utilities;

class NumberUtility
{
    /**
     * This takes a user-provided percentage (e.g. 10)
     * and converts it to the decimal (e.g. .1)
     */
    public static function convertPercentageFromUser(int|string|float|null $value): float
    {
        $sanitized = static::sanitizeNumber($value);

        return $sanitized > 1
            ? $sanitized / 100
            : $sanitized;
    }

    public static function sanitizeNumber(int|string|float|null $value): float
    {
        return (float) filter_var(
            $value,
            FILTER_SANITIZE_NUMBER_FLOAT,
            FILTER_FLAG_ALLOW_FRACTION
        );
    }
}
