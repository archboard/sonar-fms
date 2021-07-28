<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class ChangeInvoiceStatusTest extends TestCase
{
    use CreatesInvoice;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_status_change_request_is_authorized()
    {
        $invoice = $this->createInvoice();

        $this->post(route('invoices.status', $invoice))
            ->assertStatus(403);
    }

    public function test_form_validation()
    {
        $this->assignPermission('update', Invoice::class);
        $invoice = $this->createInvoice();

        $data = [
            'status' => 'voided_at',
        ];

        $this->post(route('invoices.status', $invoice), $data)
            ->assertSessionHasErrors(['duplicate']);

        $data = [
            'status' => 'wrong value',
        ];

        $this->post(route('invoices.status', $invoice), $data)
            ->assertSessionHasErrors(['status']);
    }

    public function test_can_void_and_duplicate()
    {
        $this->assignPermission('update', Invoice::class);

        $invoice = $this->createInvoice();

        $data = [
            'status' => 'voided_at',
            'duplicate' => true,
        ];

        $this->post(route('invoices.status', $invoice), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('invoices.duplicate', $invoice));

        $invoice->refresh();
        $this->assertNotNull($invoice->voided_at);
    }

    public function test_can_void_and_not_duplicate()
    {
        $this->assignPermission('update', Invoice::class);

        $invoice = $this->createInvoice();

        $data = [
            'status' => 'voided_at',
            'duplicate' => false,
        ];

        $this->post(route('invoices.status', $invoice), $data)
            ->assertSessionHas('success')
            ->assertRedirect(back()->getTargetUrl());

        $invoice->refresh();
        $this->assertNotNull($invoice->voided_at);
    }
}
