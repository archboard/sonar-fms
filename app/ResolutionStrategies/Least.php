<?php

namespace App\ResolutionStrategies;

class Least
{
    public function __invoke(int $amountDiscount, int $percentageDiscount): int
    {
        return min($amountDiscount, $percentageDiscount);
    }
}
