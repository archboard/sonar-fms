<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceRefund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;
use Tests\Traits\CreatesPayments;

class InvoiceRefundTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;
    use CreatesPayments;

    protected Invoice $invoice;

    protected bool $signIn = true;

    public function test_cant_see_create_refund_form_if_no_payments_made()
    {
        $this->assignPermission('create', InvoiceRefund::class);
        $invoice = $this->createInvoice();

        $this->get(route('invoices.refunds.create', $invoice))
            ->assertSessionHas('error')
            ->assertRedirect(route('invoices.show', $invoice));
    }
}
