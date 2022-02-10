<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoicePaymentTerm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\Assert;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;
use Tests\Traits\CreatesPayments;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;
    use CreatesPayments;

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
                ->has('breadcrumbs')
                ->has('invoice')
                ->has('paidBy')
                ->component('payments/Create')
            );
    }

    public function test_can_save_payment_to_invoice_without_associating_term()
    {
        Storage::fake(config('filesystems.receipts'));
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
            'amount' => (int) round($invoice->amount_due / 2),
            'made_by' => null,
            'notes' => $this->faker->sentence(),
            'transaction_details' => $this->faker->words(asText: true),
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
            'original_amount' => $data['amount'],
            'notes' => $data['notes'],
            'transaction_details' => $data['transaction_details'],
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

    public function test_can_make_simple_payment_to_combined_invoice()
    {
        $this->assignPermission('create', InvoicePayment::class);

        $invoice = $this->createCombinedInvoice(5);

        $date = $this->user->getCarbonFactory()
            ->today()
            ->subDay()
            ->setTimezone(config('app.timezone'));
        $childrenCount = $invoice->children()->count();
        $basePayment = $invoice->amount_due / $childrenCount;

        $data = [
            'invoice_uuid' => $invoice->uuid,
            'payment_method_id' => null,
            'invoice_payment_term_uuid' => null,
            'paid_at' => $this->getDateForInvoice($date),
            'amount' => $basePayment % $childrenCount === 0
                ? (int) $basePayment + 1
                : (int) floor($basePayment),
            'made_by' => null,
        ];

        $this->post(route('payments.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals($invoice->remaining_balance - $data['amount'], $invoice->children()->sum('remaining_balance'));

        // Assert that the terms got distributed payments correctly
        foreach ($invoice->invoicePaymentSchedules as $schedule) {
            $distributedToTerms = $schedule->invoicePaymentTerms->reduce(function (int $total, InvoicePaymentTerm $term) {
                return $total + ($term->amount_due - $term->remaining_balance);
            }, 0);

            $this->assertEquals($data['amount'], $distributedToTerms);
        }

        // Each child invoice will have received a distribution of some kind
        foreach ($invoice->children as $child) {
            if ($child->amount_due > 0) {
                $this->assertTrue($child->amount_due > $child->remaining_balance);
                $this->assertEquals(1, $child->invoicePayments()->count());
            } else {
                $this->assertEquals(0, $child->invoicePayments()->count());
            }
        }
    }

    public function test_can_make_full_payment_to_combined_invoice()
    {
        $this->assignPermission('create', InvoicePayment::class);

        $invoice = $this->createCombinedInvoice(5);
        $this->assertEquals($invoice->amount_due, $invoice->remaining_balance);

        $date = $this->user->getCarbonFactory()
            ->today()
            ->subDay()
            ->setTimezone(config('app.timezone'));
        $data = [
            'invoice_uuid' => $invoice->uuid,
            'payment_method_id' => null,
            'invoice_payment_term_uuid' => null,
            'paid_at' => $this->getDateForInvoice($date),
            'amount' => $invoice->amount_due,
            'made_by' => null,
        ];

        $this->post(route('payments.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals($invoice->remaining_balance - $data['amount'], $invoice->children()->sum('remaining_balance'));

        // Assert that the terms got distributed payments correctly
        foreach ($invoice->invoicePaymentSchedules as $schedule) {
            $distributedToTerms = $schedule->invoicePaymentTerms->reduce(function (int $total, InvoicePaymentTerm $term) {
                return $total + max($term->amount_due - $term->remaining_balance, 0);
            }, 0);

            $this->assertEquals($data['amount'], $distributedToTerms);
        }

        // Each child invoice will have received a distribution of some kind
        foreach ($invoice->children as $child) {
            $this->assertEquals(0, $child->remaining_balance);
            $this->assertEquals($child->amount_due > 0 ? 1 : 0, $child->invoicePayments()->count());
        }
    }

    public function test_can_fetch_payments_for_invoice()
    {
        $this->assignPermission('viewAny', InvoicePayment::class);

        $invoice = $this->createInvoice();
        $this->createPayment(invoice: $invoice);
        $this->createPayment(invoice: $invoice);

        $this->get(route('invoices.payments', $invoice))
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_can_fetch_related_payments()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('viewAny', InvoicePayment::class);

        $invoice = $this->createCombinedInvoice();
        $this->createPayment(invoice: $invoice);
        $this->createPayment(invoice: $invoice);
        $child = $invoice->children->random();
        $this->createPayment(invoice: $child);

        $json = $this->get(route('invoices.payments.related', $invoice))
            ->assertOk()
            ->assertJsonCount(1)
            ->json();

        $payment = Arr::first($json);
        $this->assertEquals($payment['invoice']['uuid'], $child->uuid);
    }

    public function test_can_get_edit_payment_page()
    {
        $this->assignPermission('update', InvoicePayment::class);

        $payment = $this->createPayment();

        $this->get(route('payments.edit', $payment))
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('invoice')
                ->has('paidBy')
                ->has('payment')
                ->where('method', 'put')
                ->where('endpoint', route('payments.update', $payment))
                ->where('breadcrumbs', fn ($prop) => count($prop) > 1)
                ->component('payments/Create')
            );
    }
}
