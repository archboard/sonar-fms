<?php

namespace Tests\Feature;

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
        $this->assignPermission('create', Invoice::class);
        $invoice = $this->createInvoice(['published_at' => null, 'student_id' => $this->student->id]);
        $data = $this->generateInvoiceRequestAttributesForStudent($this->student);

        $this->put(route('invoices.update.draft', $invoice), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        // The original invoice should not exist
        $this->assertDatabaseMissing('invoices', ['uuid' => $invoice->uuid]);
        $this->assertEquals(1, $this->student->invoices()->count());
    }
}
