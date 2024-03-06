<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class InvoiceSelectionTest extends TestCase
{
    use CreatesInvoice;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    protected function setInvoiceSelection(Collection $invoices): static
    {
        $data = $invoices->map(fn (Invoice $invoice) => [
            'school_id' => $this->school->id,
            'user_uuid' => $this->user->id,
            'invoice_uuid' => $invoice->uuid,
        ]);

        DB::table('invoice_selections')->insert($data->toArray());

        return $this;
    }

    public function test_can_get_invoice_selection()
    {
        $batchId = $this->createBatchInvoices();

        $invoices = Invoice::batch($batchId)->get();

        $json = $this->setInvoiceSelection($invoices)
            ->get(route('invoice-selection.index'))
            ->assertOk()
            ->json();

        $this->assertCount($invoices->count(), $json);
    }

    public function test_can_add_invoice_to_selection_via_uuid()
    {
        $invoice = $this->createInvoice();

        $this->assertEquals(0, $this->user->invoiceSelections()->count());

        $this->put(route('invoice-selection.update', $invoice->uuid))
            ->assertOk();

        $this->assertDatabaseHas('invoice_selections', [
            'school_id' => $this->school->id,
            'user_uuid' => $this->user->id,
            'invoice_uuid' => $invoice->uuid,
        ]);

        $this->assertEquals($invoice->uuid, $this->user->selectedInvoices()->first()->uuid);
    }

    public function test_can_mass_add_invoices_to_selection()
    {
        $otherSchool = School::where('id', '!=', $this->school->id)
            ->first();
        $batchId = $this->createBatchInvoices();

        // Create another invoice for a different school
        $this->createInvoice(['school_id' => $otherSchool->id]);

        $this->post(route('invoice-selection.store'), [
            'batch_id' => $batchId,
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $invoices = $this->user->selectedInvoices()->get();

        $this->assertEquals(2, $invoices->count());
    }

    public function test_can_remove_invoice_selection()
    {
        $batchId = $this->createBatchInvoices();

        $invoices = Invoice::batch($batchId)->get();

        $this->setInvoiceSelection($invoices)
            ->delete(route('invoice-selection.destroy', $invoices->random()->uuid))
            ->assertOk();

        $this->assertEquals(1, $this->user->selectedInvoices()->count());
    }

    public function test_can_remove_entire_selection()
    {
        $batchId = $this->createBatchInvoices();

        $invoices = Invoice::batch($batchId)->get();

        $this->setInvoiceSelection($invoices);

        $this->assertEquals(2, $this->user->selectedInvoices()->count());

        $this->delete(route('invoice-selection.remove'))
            ->assertOk()
            ->assertJsonStructure(['level', 'message']);

        $this->assertEquals(0, $this->user->selectedInvoices()->count());
    }

    public function test_can_check_published_status()
    {
        $batchId = $this->createBatchInvoices();
        $invoices = Invoice::batch($batchId)->get();
        $this->setInvoiceSelection($invoices);
        $invoices->random()->update(['published_at' => null]);

        $this->get(route('invoice-selection.published'))
            ->assertOk()
            ->assertJson(['published' => false]);

        Invoice::whereIn('uuid', $invoices->pluck('uuid'))
            ->update(['published_at' => now()]);

        $this->get(route('invoice-selection.published'))
            ->assertOk()
            ->assertJson(['published' => true]);
    }

    public function test_publish_selected()
    {
        $this->withoutExceptionHandling();
        $batchId = $this->createBatchInvoices();
        $invoices = Invoice::batch($batchId)->get();
        $this->setInvoiceSelection($invoices);
        $invoices->random()->update(['published_at' => null]);

        $this->assertFalse(
            $this->user->selectedInvoices()
                ->whereNull('published_at')
                ->doesntExist()
        );

        $this->put(route('invoice-selection.publish'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(
            $this->user->selectedInvoices()
                ->whereNull('published_at')
                ->doesntExist()
        );
        $this->assertEquals($invoices->count(), $this->user->selectedInvoices()->count());
    }
}
