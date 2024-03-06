<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use App\Models\InvoiceScholarship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;
use Tests\Traits\SignsIn;

class InvoiceDuplicationTest extends TestCase
{
    use CreatesInvoice;
    use RefreshDatabase;
    use SignsIn;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_can_generate_correct_invoice_template()
    {
        $template = $this->createInvoice()->asInvoiceTemplate();

        $this->assertIsArray($template);

        foreach (Invoice::$formAttributes as $attribute) {
            $this->assertArrayHasKey($attribute, $template);
        }

        foreach ($template['items'] as $item) {
            foreach (InvoiceItem::$formAttributes as $attribute) {
                $this->assertArrayHasKey($attribute, $item);
            }
        }

        foreach ($template['scholarships'] as $scholarship) {
            foreach (InvoiceScholarship::$formAttributes as $attribute) {
                $this->assertArrayHasKey($attribute, $scholarship);
            }
        }

        foreach ($template['payment_schedules'] as $schedule) {
            foreach (InvoicePaymentSchedule::$formAttributes as $attribute) {
                $this->assertArrayHasKey($attribute, $schedule);

                foreach ($schedule['terms'] as $term) {
                    foreach (InvoicePaymentTerm::$formAttributes as $attribute) {
                        $this->assertArrayHasKey($attribute, $term);
                    }
                }
            }
        }
    }

    public function test_can_get_to_invoice_duplication_page()
    {
        $this->assignPermission('create', Invoice::class);

        $invoice = $this->createInvoice();

        $this->get(route('invoices.duplicate', $invoice))
            ->assertInertia(fn (Assert $page) => $page
                ->component('invoices/Create')
                ->where('duplicating', true)
                ->where('defaultTemplate', $invoice->asInvoiceTemplate())
                ->has('students')
                ->has('method')
                ->has('endpoint')
            );
    }
}
