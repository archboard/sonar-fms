<?php

namespace App\ResolutionStrategies;

class Greatest
{
    public function __invoke(int $amountDiscount, int $percentageDiscount): int
    {
        return max($amountDiscount, $percentageDiscount);
    }
}
