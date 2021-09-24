<?php

namespace Tests\Traits;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use App\Models\InvoiceScholarship;
use App\Models\Student;
use App\ResolutionStrategies\Greatest;
use App\ResolutionStrategies\Least;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;

trait CreatesInvoice
{
    use WithFaker;

    protected function generateInvoiceRequestAttributesForStudent(Student $student): array
    {
        $attributes = $this->generateInvoiceRequestAttributes();
        $attributes['students'] = [$student->id];

        return $attributes;
    }

    protected function generateInvoiceRequestAttributes(): array
    {
        $items = Collection::times($this->faker->numberBetween(1, 3))
            ->map(fn () => [
                'id' => $this->uuid(),
                'name' => $this->faker->word,
                'description' => $this->faker->sentence,
                'amount_per_unit' => $this->faker->numberBetween(1000),
                'quantity' => $this->faker->numberBetween(1, 4),
            ]);

        $scholarships = Collection::times($this->faker->numberBetween(1, 3))
            ->map(fn () => [
                'id' => $this->uuid(),
                'scholarship_id' => null,
                'name' => $this->faker->words(asText: true),
                'amount' => $this->faker->numberBetween(1000),
                'percentage' => $this->faker->numberBetween(0, 100),
                'resolution_strategy' => $this->faker->randomElement([Least::class, Greatest::class]),
                'applies_to' => $this->faker->boolean
                    ? $items->random($this->faker->numberBetween(1, $items->count()))
                        ->pluck('id')->toArray()
                    : [],
            ]);

        $taxToAll = $this->faker->boolean;

        return [
            'students' => [],
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => $items->toArray(),
            'scholarships' => $scholarships->toArray(),
            'payment_schedules' => [],
            'apply_tax' => true,
            'use_school_tax_defaults' => true,
            'tax_rate' => $this->faker->numberBetween(1, 10),
            'tax_label' => 'VAT',
            'apply_tax_to_all_items' => $taxToAll,
            'tax_items' => $taxToAll
                ? []
                : $items->random($this->faker->numberBetween(1, $items->count()))
                    ->map(fn ($item) => [
                        'item_id' => $item['id'],
                        'selected' => true,
                        'tax_rate' => $this->faker->numberBetween(1, 10),
                    ])->toArray()
        ];
    }

    protected function createInvoice(array $invoiceAttributes = []): Invoice
    {
        $attributes = array_merge(['user_id' => $this->user->id], $invoiceAttributes);

        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create($attributes);

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
