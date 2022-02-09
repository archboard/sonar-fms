<?php

namespace Tests\Feature;

use App\Models\InvoicePayment;
use App\Models\Receipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesPayments;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPayments;

    protected bool $signIn = true;
    protected InvoicePayment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payment = $this->createPayment();
        Storage::fake(config('filesystems.receipts'));
    }
}
