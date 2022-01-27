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
    use WithFaker;
    use CreatesInvoice;
    use CreatesPayments;

    protected Invoice $invoice;

    protected bool $signIn = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoice = $this->createInvoice();
        $this->invoice->unsetRelations();
        $this->createPayment(invoice: $this->invoice);
    }

    public function test_cant_access_create_without_permission()
    {
        $this->get(route('invoices.refunds.create', $this->invoice))
            ->assertForbidden();
    }

    public function test_cant_see_create_refund_form_if_no_payments_made()
    {
        $this->assignPermission('create', InvoiceRefund::class);
        $this->invoice->invoicePayments()->delete();

        $this->get(route('invoices.refunds.create', $this->invoice))
            ->assertSessionHas('error')
            ->assertRedirect(route('invoices.show', $this->invoice));
    }

    public function test_can_see_create_refund_form()
    {
        $this->assignPermission('create', InvoiceRefund::class);

        $this->get(route('invoices.refunds.create', $this->invoice))
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('invoice')
                ->has('breadcrumbs')
                ->component('refunds/Create')
            );
    }

    public function test_amount_validation()
    {
        $this->assignPermission('create', InvoiceRefund::class);
        $data = [
            'amount' => $this->invoice->total_paid + 1,
            'refunded_at' => $this->dateFromDatePicker(now()),
            'transaction_details' => $this->faker->creditCardNumber(),
            'notes' => $this->faker->sentence(),
        ];

        $this->post(route('invoices.refunds.store', $this->invoice), $data)
            ->assertSessionHasErrors('amount');
    }

    public function test_can_save_refund_correctly()
    {
        $this->assignPermission('create', InvoiceRefund::class);

        $data = [
            'amount' => rand(1, $this->invoice->total_paid),
            'refunded_at' => $this->dateFromDatePicker(now()),
            'transaction_details' => $this->faker->creditCardNumber(),
            'notes' => $this->faker->sentence(),
        ];

        $this->post(route('invoices.refunds.store', $this->invoice), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(1, $this->invoice->invoiceRefunds()->count());
        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $data['invoice_uuid'] = $this->invoice->uuid;
        $this->assertDatabaseHas('invoice_refunds', $data);

        $this->invoice->refresh();
        $this->assertEquals(
            $this->invoice->amount_due + $data['amount'] - $this->invoice->invoicePayments()->sum('amount'),
            $this->invoice->remaining_balance
        );

        $this->assertTrue(
            $this->invoice->activities->some(fn ($a) => str_contains($a->description, 'recorded a refund'))
        );
    }

    public function test_can_fetch_refunds()
    {
        $this->assignPermission('viewAny', InvoiceRefund::class);

        $this->invoice->invoiceRefunds()
            ->save(InvoiceRefund::factory()->make(['amount' => (int) round($this->invoice->total_paid / 2)]));

        $this->get(route('invoices.refunds.index', $this->invoice))
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_can_fetch_related_refunds()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('viewAny', InvoiceRefund::class);

        $invoice = $this->createCombinedInvoice();
        /** @var Invoice $child */
        $child = $invoice->children->random();

        $this->createPayment(invoice: $child);

        $child->invoiceRefunds()
            ->saveMany(
                InvoiceRefund::factory()
                    ->count(2)
                    ->make([
                        'amount' => (int) round($this->invoice->total_paid / 3)
                    ])
            );

        $this->get(route('invoices.refunds.related', $invoice))
            ->assertOk()
            ->assertJsonCount(2);
    }
}
