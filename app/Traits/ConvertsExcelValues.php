<?php

namespace App\Traits;

use App\Utilities\NumberUtility;
use Brick\Money\Money;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

trait ConvertsExcelValues
{
    /**
     * Converts Excel's number format to a
     * Carbon instance and returns the date string
     */
    protected function convertDate($value): ?string
    {
        if (is_numeric($value)) {
            $date = Carbon::create(1900);
            $days = floatval($value) - 2;
            $hours = $days * 24;
            $minutes = $hours * 60;

            return $date->addMinutes((int) $minutes)
                ->toDateString();
        }

        try {
            return Carbon::parse($value, $this->user->timezone)
                ->setTimezone(config('app.timezone'))
                ->toDateTimeString();
        } catch (InvalidFormatException $exception) {
            return null;
        }
    }

    protected function convertCurrency($value): ?int
    {
        $sanitized = abs(NumberUtility::sanitizeNumber($value));

        try {
            return Money::of($sanitized, $this->school->currency->code)
                ->getMinorAmount()
                ->toInt();
        } catch (\Exception $exception) {
            return null;
        }
    }

    protected function convertPercentage($value): float
    {
        return NumberUtility::convertPercentageFromUser($value);
    }

    /**
     * Converts Excel's number format to a
     * Carbon instance and returns the date/time string
     */
    protected function convertDateTime($value): ?string
    {
        if (is_numeric($value)) {
            $date = Carbon::create(1900, 1, 1, 0, 0, 0, $this->user->timezone);
            $days = floatval($value) - 2;
            $hours = $days * 24;
            $minutes = $hours * 60;
            return $date->addMinutes((int) $minutes)
                ->roundUnit('minute', 15)
                ->setTimezone(config('app.timezone'))
                ->toDateTimeString();
        }

        try {
            return Carbon::parse($value, $this->user->timezone)
                ->setTimezone(config('app.timezone'))
                ->toDateTimeString();
        } catch (InvalidFormatException $exception) {
            return null;
        }
    }

    protected function convertInt($value): int
    {
        return (int) $value;
    }
}
