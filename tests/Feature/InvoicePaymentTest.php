<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoicePaymentTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_can_save_payment_to_invoice_without_associating_term()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', InvoicePayment::class);

        $invoice = $this->createInvoice();
        $date = $this->user->getCarbonFactory()
            ->today()
            ->subDay()
            ->setTimezone(config('app.timezone'));
        $data = [
            'invoice_uuid' => $invoice->uuid,
            'payment_method_id' => null,
            'paid_at' => $this->getDateForInvoice($date),
            'amount' => round($invoice->amount_due / 2),
            'made_by' => null,
        ];

        $this->post(route('payments.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $invoice->refresh();
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

    public function test_can_save_payment_to_invoice_with_associating_term()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', InvoicePayment::class);

        $invoice = $this->createInvoice();
        $this->seedPaymentSchedules($invoice, 1);
        /** @var InvoicePaymentTerm $term */
        $term = $invoice->invoicePaymentTerms()->first();

        $date = $this->user->getCarbonFactory()
            ->today()
            ->subDay()
            ->setTimezone(config('app.timezone'));
        $data = [
            'invoice_uuid' => $invoice->uuid,
            'payment_method_id' => null,
            'invoice_payment_term_uuid' => $term->uuid,
            'paid_at' => $this->getDateForInvoice($date),
            'amount' => (int) round($invoice->amount_due / 2),
            'made_by' => null,
        ];

        $this->post(route('payments.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(1, $invoice->invoicePayments()->count());
        $this->assertDatabaseHas('invoice_payments', [
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'invoice_uuid' => $invoice->uuid,
            'amount' => $data['amount'],
            'paid_at' => $date->toDateTimeString(),
            'invoice_payment_term_uuid' => $term->uuid,
        ]);

        // The "amount due" that will be referenced is the schedule's amount due, not the invoice's
        $this->assertEquals($term->invoicePaymentSchedule->amount - $data['amount'], $invoice->remaining_balance);
        $this->assertEquals($term->invoice_payment_schedule_uuid, $invoice->invoice_payment_schedule_uuid);
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
            'paid_at' => $this->getDateForInvoice($date),
            'amount' => $invoice->amount_due + 10,
            'made_by' => null,
        ];

        $this->post(route('payments.store'), $data)
            ->assertSessionHasErrors(['amount']);
    }

    public function test_can_distribute_payment_across_terms_correctly()
    {
        $invoice = $this->createInvoice()->refresh();
        $this->seedPaymentSchedules($invoice, 2);
        $paymentAmount = (int) round($invoice->amount_due / 3);
        $payments = $invoice->invoicePayments()
            ->saveMany(
                InvoicePayment::factory()
                    ->count(2)
                    ->make([
                        'amount' => $paymentAmount,
                        'recorded_by' => $this->user->uuid,
                    ])
            );
        $totalPaid = $paymentAmount * $payments->count();

        $invoice->distributePaymentsToTerms()
            ->setRemainingBalance()
            ->save();

        $this->assertEquals($totalPaid, $invoice->total_paid);
        $this->assertEquals($totalPaid, $invoice->student->revenue);

        foreach ($invoice->invoicePaymentSchedules as $schedule) {
            $paidToSchedule = $schedule->invoicePaymentTerms
                ->reduce(fn (int $total, InvoicePaymentTerm $term) => $total + $term->amount_due - $term->remaining_balance, 0);

            $this->assertEquals($totalPaid, $paidToSchedule);
        }
    }

    public function test_can_add_payment_to_child_invoice()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', InvoicePayment::class);

        $parent = $this->createCombinedInvoice(2);
        /** @var Invoice $child */
        $child = $parent->children->random();

        $date = $this->user->getCarbonFactory()
            ->today()
            ->subDay()
            ->setTimezone(config('app.timezone'));
        $data = [
            'invoice_uuid' => $child->uuid,
            'payment_method_id' => null,
            'invoice_payment_term_uuid' => null,
            'paid_at' => $this->getDateForInvoice($date),
            'amount' => (int) round($child->amount_due / 2),
            'made_by' => null,
        ];

        $this->post(route('payments.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $child->refresh();
        $parent->refresh();
        $this->assertEquals($child->amount_due - $data['amount'], $child->remaining_balance);
        $this->assertEquals($parent->amount_due - $data['amount'], $parent->remaining_balance);
        $this->assertEquals($data['amount'], $child->total_paid);
        $this->assertEquals($data['amount'], $parent->total_paid);

        $this->assertTrue(
            $child->activities->some(fn ($a) => Str::contains($a->description, 'recorded a payment'))
        );
        $this->assertTrue(
            $parent->activities->some(fn ($a) => Str::contains($a->description, "made to {$child->invoice_number}"))
        );
    }
}
