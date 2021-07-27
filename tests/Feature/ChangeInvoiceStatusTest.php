<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChangeInvoiceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    protected function createInvoice(): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'batch_id' => $this->uuid(),
        ]);

        return $invoice;
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
}
