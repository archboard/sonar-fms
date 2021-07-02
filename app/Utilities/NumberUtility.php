<?php

namespace App\Utilities;

class NumberUtility
{
    /**
     * This takes a user-provided percentage (e.g. 10)
     * and converts it to the decimal (e.g. .1)
     *
     * @param int|string|float|null $value
     * @return float
     */
    public static function convertPercentageFromUser(int|string|float|null $value): float
    {
        $sanitized = (float) filter_var(
            $value,
            FILTER_SANITIZE_NUMBER_FLOAT,
            FILTER_FLAG_ALLOW_FRACTION
        );

        return $sanitized > 1
            ? $sanitized / 100
            : $sanitized;
    }
}
