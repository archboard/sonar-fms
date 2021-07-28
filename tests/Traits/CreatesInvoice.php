<?php

namespace Tests\Traits;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use App\Models\InvoiceScholarship;
use Illuminate\Foundation\Testing\WithFaker;

trait CreatesInvoice
{
    use WithFaker;

    protected function createInvoice(): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $invoice->invoiceItems()
            ->saveMany(
                InvoiceItem::factory()
                    ->count($this->faker->numberBetween(1, 5))
                    ->make()
            );

        InvoiceScholarship::factory()
            ->count($this->faker->numberBetween(0, 3))
            ->make(['invoice_uuid' => $invoice->uuid])
            ->each(function (InvoiceScholarship $scholarship) {
                $scholarship->setAmount()->save();
            });


        InvoicePaymentSchedule::factory()
            ->count($this->faker->numberBetween(0, 3))
            ->make(['invoice_uuid' => $invoice->uuid])
            ->each(function (InvoicePaymentSchedule $schedule) {
                $terms = InvoicePaymentTerm::factory()
                    ->count($this->faker->numberBetween(1, 5))
                    ->make([
                        'invoice_uuid' => $schedule->invoice_uuid,
                        'invoice_payment_schedule_uuid' => $schedule['uuid'],
                    ]);

                $schedule->setRelation('invoicePaymentTerms', $terms);
                $schedule->setAmount()->save();

                $terms->each(fn ($term) => $term->save());
            });

        return $invoice->setCalculatedAttributes(true)
            ->refresh();
    }
}
