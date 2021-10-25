<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\Assert;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class InvoiceDraftTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = $this->createStudent();
        $this->signIn();
    }

    public function test_need_permission_to_save_as_draft()
    {
        $data = $this->generateInvoiceRequestAttributes();

        $this->post(route('invoices.store.draft'), $data)
            ->assertForbidden();
    }

    public function test_can_save_invoice_as_draft()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);
        $data = $this->generateInvoiceRequestAttributesForStudent($this->student);

        $this->post(route('invoices.store.draft'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        /** @var Invoice $invoice */
        $invoice = $this->student
            ->invoices()
            ->first();

        $this->assertNotNull($invoice);
        $this->assertNull($invoice->published_at);

        /** @var Activity $activity */
        $activity = $invoice->activities()->with('causer')->first();
        $this->assertEquals("Created as a draft by {$this->user->full_name}.", $activity->description);
    }

    public function test_cant_edit_published_invoice()
    {
        $this->assignPermission('update', Invoice::class);
        $invoice = $this->createInvoice(['published_at' => now()]);

        $this->get(route('invoices.edit', $invoice))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_can_edit_draft_invoice()
    {
        $this->assignPermission('update', Invoice::class);
        $invoice = $this->createInvoice(['published_at' => null]);

        $this->get(route('invoices.edit', $invoice))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('method')
                ->has('endpoint')
            );
    }

    public function test_can_save_draft_invoice_as_draft_again()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('update', Invoice::class);
        $invoice = $this->createInvoice(['published_at' => null, 'student_uuid' => $this->student->id]);
        $data = $this->generateInvoiceRequestAttributesForStudent($this->student);

        $this->put(route('invoices.update.draft', $invoice), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        // The original invoice should not exist
        $this->assertDatabaseHas('invoices', ['uuid' => $invoice->uuid]);
        $this->assertEquals(1, $this->student->invoices()->count());
        $this->assertEquals(2, $invoice->activities()->count());
    }

    public function test_cant_edit_batch_without_permission()
    {
        $batchId = $this->createBatchInvoices();

        $this->get(route('batches.edit', $batchId))
            ->assertForbidden();
    }

    public function test_cant_edit_batch_draft_invoices_that_are_published()
    {
        $this->assignPermission('update', Invoice::class);
        $batchId = $this->createBatchInvoices();

        Invoice::batch($batchId)->update(['published_at' => now()]);

        $this->get(route('batches.edit', $batchId))
            ->assertSessionHas('error')
            ->assertRedirect();
    }

    public function test_can_edit_batch_draft_invoices()
    {
        $this->assignPermission('update', Invoice::class);
        $batchId = $this->createBatchInvoices(invoiceAttributes: ['published_at' => null]);

        $this->get(route('batches.edit', $batchId))
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('invoice')
                ->has('method')
                ->has('endpoint')
            );
    }

    public function test_can_update_batch_draft_invoices()
    {
        $this->assignPermission('update', Invoice::class);

        $batchId = $this->createBatchInvoices(invoiceAttributes: ['published_at' => null]);
        $newAttributes = $this->generateInvoiceRequestAttributes();
        $students = Invoice::batch($batchId)->pluck('student_uuid');
        $newAttributes['students'] = $students->toArray();

        $this->post(route('batches.draft', $batchId), $newAttributes)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('invoices', ['batch_id' => $batchId]);
        $this->assertEquals($students->count(), Invoice::whereIn('student_uuid', $students)->unpublished()->count());
    }

    public function test_can_update_unpublished_batch_draft_invoices()
    {
        $this->assignPermission('update', Invoice::class);

        $batchId = $this->createBatchInvoices(invoiceAttributes: ['published_at' => null]);
        $students = Invoice::batch($batchId)->pluck('student_uuid');
        $student = Student::find($students->random());

        // Publish one of the invoices
        $student->invoices()->update(['published_at' => now()]);

        $newAttributes = $this->generateInvoiceRequestAttributes();
        $newAttributes['students'] = $students
            ->filter(fn ($id) => $id !== $student->id)
            ->toArray();

        $this->post(route('batches.draft', $batchId), $newAttributes)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals($batchId, $student->invoices()->latest()->first()->batch_id);
        $this->assertEquals($students->count() - 1, Invoice::whereIn('student_uuid', $students)->unpublished()->count());
    }

    public function test_can_publish_batch_draft()
    {
        $this->assignPermission('update', Invoice::class);

        $batchId = $this->createBatchInvoices(invoiceAttributes: ['published_at' => null]);
        $students = Invoice::batch($batchId)->pluck('student_uuid');

        $newAttributes = $this->generateInvoiceRequestAttributes();
        $newAttributes['students'] = $students->toArray();

        $this->put(route('batches.update', $batchId), $newAttributes)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(0, Invoice::batch($batchId)->count());
        $this->assertEquals($students->count(), Invoice::whereIn('student_uuid', $students)->published()->count());
    }
}
