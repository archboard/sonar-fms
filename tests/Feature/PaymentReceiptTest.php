<?php

namespace Tests\Feature;

use App\Models\InvoicePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;
use Tests\Traits\CreatesPayments;

class PaymentReceiptTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPayments;

    protected bool $signIn = true;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    public function test_can_view_payment_receipt()
    {
        $this->assignPermission('viewAny', InvoicePayment::class);
        $payment = $this->createPayment();

        $this->get("/payments/{$payment->id}/receipt")
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('payment')
                ->component('payments/Receipt')
            );
    }
}
