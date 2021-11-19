<?php

namespace Tests\Feature;

use App\Models\InvoicePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\Assert;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;

    protected bool $signIn = true;

    public function test_cant_list_all_payments_without_permissions()
    {
        $this->get('/payments')
            ->assertForbidden();
    }

    public function test_can_list_payments_with_permission()
    {
        $this->assignPermission('viewAny', InvoicePayment::class);

        $this->get('/payments')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('payments')
                ->component('payments/Index')
            );
    }

    public function test_can_access_create_page()
    {
        $this->assignPermission('create', InvoicePayment::class);

        $this->get('/payments/create')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('paymentMethods')
                ->has('breadcrumbs')
                ->has('invoice')
                ->has('paidBy')
                ->component('payments/Create')
            );
    }

    public function test_can_save_payment_to_invoice()
    {
        $this->assignPermission('create', InvoicePayment::class);

        $invoice = $this->createInvoice();
        $date = $this->user->getCarbonFactory()
            ->today()
            ->subDay()
            ->setTimezone(config('app.timezone'));
        $data = [
            'invoice_uuid' => $invoice->uuid,
            'payment_method_id' => null,
            'paid_at' => $date->format('Y-m-d\TH:i:s.v\Z'),
            'amount' => round($invoice->amount_due / 2),
            'made_by' => null,
        ];

        $this->post(route('payments.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(1, $invoice->invoicePayments()->count());
        $this->assertDatabaseHas('invoice_payments', [
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'invoice_uuid' => $invoice->uuid,
            'amount' => $data['amount'],
            'paid_at' => $date->toDateTimeString(),
        ]);
        $this->assertEquals($invoice->amount_due - $data['amount'], $invoice->refresh()->remaining_balance);
    }

    public function test_cant_save_payment_with_invalid_amount()
    {
        $this->assignPermission('create', InvoicePayment::class);

        $invoice = $this->createInvoice();
        $date = $this->user->getCarbonFactory()
            ->today()
            ->subDay()
            ->setTimezone(config('app.timezone'));
        $data = [
            'invoice_uuid' => $invoice->uuid,
            'payment_method_id' => null,
            'paid_at' => $date->format('Y-m-d\TH:i:s.v\Z'),
            'amount' => $invoice->amount_due + 10,
            'made_by' => null,
        ];

        $this->post(route('payments.store'), $data)
            ->assertSessionHasErrors(['amount']);
    }
}
