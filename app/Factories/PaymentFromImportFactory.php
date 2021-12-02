<?php

namespace App\Factories;

use App\Models\PaymentImport;

class PaymentFromImportFactory
{
    public function __construct(protected PaymentImport $import)
    {
    }
}
