<?php

namespace Tests\Unit;

use App\Utilities\NumberUtility;
use PHPUnit\Framework\TestCase;

class NumberUtilityTest extends TestCase
{
    public function test_number_utility_percentage_conversion()
    {
        $this->assertEquals(.1, NumberUtility::convertPercentageFromUser(10));
        $this->assertEquals(.155, NumberUtility::convertPercentageFromUser(15.5));
        $this->assertEquals(.155, NumberUtility::convertPercentageFromUser('15.5'));
        $this->assertEquals(.05, NumberUtility::convertPercentageFromUser('.05'));
        $this->assertEquals(1, NumberUtility::convertPercentageFromUser('1'));
    }
}
