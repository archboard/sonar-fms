<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceRefund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\Assert;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoice = $this->createInvoice();
    }

    public function test_cant_access_create_without_permission()
    {
        $this->get(route('invoices.refunds.create', $this->invoice))
            ->assertForbidden();
    }

    public function test_cant_see_create_refund_form_if_no_payments_made()
    {
        $this->assignPermission('create', InvoiceRefund::class);

        $this->get(route('invoices.refunds.create', $this->invoice))
            ->assertSessionHas('error')
            ->assertRedirect(route('invoices.show', $this->invoice));
    }

    public function test_can_see_create_refund_form()
    {
        $this->assignPermission('create', InvoiceRefund::class);
        $this->createPayment(invoice: $this->invoice);

        $this->get(route('invoices.refunds.create', $this->invoice))
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('invoice')
                ->component('refunds/Create')
            );
    }
}
