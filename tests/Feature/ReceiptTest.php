<?php

namespace Tests\Feature;

use App\Models\InvoicePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesPayments;

class ReceiptTest extends TestCase
{
    use CreatesPayments;
    use RefreshDatabase;

    protected bool $signIn = true;

    protected InvoicePayment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payment = $this->createPayment();
        Storage::fake(config('filesystems.receipts'));
    }
}
