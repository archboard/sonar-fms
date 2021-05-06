<?php

namespace Tests\Feature;

use App\Jobs\SendNewInvoiceNotification;
use App\Models\Fee;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateInvoiceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_can_create_invoice_for_student_with_notify_now()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        $term = $this->school->terms()->save(
            Term::factory()->make()
        );
        $invoice = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => $term->id,
            'notify_now' => true,
            'items' => [
                [
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
            ],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoice)
            ->assertRedirect()
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals($invoice['title'], $invoice->title);
        $this->assertEquals($invoice['description'], $invoice->description);
        $this->assertEquals($invoice['term_id'], $invoice->term_id);
        $this->assertEquals($invoice['notify_now'], $invoice->notify_now);
        $this->assertEquals(now()->addMonth()->startOfMinute(), $invoice->due_at->startOfMinute());
        $this->assertEquals(1, $invoice->invoiceItems()->count());

        Queue::assertPushed(SendNewInvoiceNotification::class);
    }

    public function test_can_create_invoice_for_student_without_notify_now()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        /** @var Fee $fee */
        $fee = $this->school->fees()->save(
            Fee::factory()->make(['tenant_id' => $this->tenant->id])
        );
        $invoice = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify_now' => false,
            'items' => [
                [
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
                [
                    'fee_id' => $fee->id,
                    'sync_with_fee' => true,
                    'name' => 'Not matching name',
                    'amount_per_unit' => 100,
                    'quantity' => 2,
                ]
            ],
        ];

        ray()->showQueries();
        $this->post(route('students.invoices.store', [$student]), $invoice)
            ->assertRedirect()
            ->assertSessionHas('success');
        ray()->stopShowingQueries();

        Queue::assertNotPushed(SendNewInvoiceNotification::class);

        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals($invoice['title'], $invoice->title);
        $this->assertEquals($invoice['description'], $invoice->description);
        $this->assertEquals($invoice['term_id'], $invoice->term_id);
        $this->assertEquals($invoice['notify_now'], $invoice->notify_now);
        $this->assertEquals(now()->addMonth()->startOfMinute(), $invoice->due_at->startOfMinute());
        $this->assertEquals(2, $invoice->invoiceItems()->count());

        // Test that the name and amount matches from the underlying fee, not the one we sent
        /** @var InvoiceItem $itemWithSync */
        $itemWithSync = $invoice->invoiceItems()
            ->firstWhere('fee_id', $fee->id);
        $this->assertEquals($fee->name, $itemWithSync->name);
        $this->assertEquals($fee->amount, $itemWithSync->amount_per_unit);
    }
}
