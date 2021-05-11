<?php

namespace App\ResolutionStrategies;

use App\Models\Invoice;

class Least
{
    public function __invoke(Invoice $invoice)
    {

    }
}
