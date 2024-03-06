<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class InvoicePdfTest extends TestCase
{
    use CreatesInvoice;
    use RefreshDatabase;

    protected bool $signIn = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assignPermission('view', Invoice::class);
    }

    public function test_can_generate_invoice_in_download_route()
    {
        Storage::fake();
        $invoice = $this->createInvoice();
        InvoiceLayout::factory()->create(['is_default' => true]);

        $this->get(route('invoices.download', $invoice))
            ->assertDownload($invoice->invoice_number.'.pdf');

        $this->assertDatabaseHas('invoice_pdfs', [
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'invoice_uuid' => $invoice->uuid,
            'user_uuid' => $this->user->uuid,
        ]);
        $this->assertEquals(1, $invoice->invoicePdfs()->count());
        $pdf = $invoice->invoicePdfs()->first();

        Invoice::getPdfDisk()->assertExists($pdf->relative_path);
        $this->assertStringContainsString($invoice->invoice_number, $pdf->name);
    }
}
