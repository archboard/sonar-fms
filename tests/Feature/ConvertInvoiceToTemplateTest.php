<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;
use Tests\Traits\SignsIn;

class ConvertInvoiceToTemplateTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;
    use SignsIn;

    public function test_can_save_invoice_as_invoice_template()
    {
        $this->assignPermission('viewAny', Invoice::class);
        $this->assignPermission('create', InvoiceTemplate::class);

        $invoice = $this->createInvoice();

        $this->post(route('invoices.convert', $invoice), ['name' => 'My template'])
            ->assertSessionHas('success')
            ->assertRedirect();

        /** @var InvoiceTemplate $template */
        $template = $this->school->invoiceTemplates->first();

        $this->assertEquals(Arr::except($invoice->asInvoiceTemplate(), 'students'), $template->template);
        $this->assertFalse($template->for_import);
        $this->assertEquals($this->user->id, $template->user_id);
        $this->assertEquals('My template', $template->name);
    }
}
