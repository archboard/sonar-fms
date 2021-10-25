<?php

namespace Tests\Traits;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use App\Models\InvoiceScholarship;
use App\Models\InvoiceSelection;
use App\Models\Student;
use App\ResolutionStrategies\Greatest;
use App\ResolutionStrategies\Least;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        $attributes = array_merge(['user_uuid' => $this->user->uuid], $invoiceAttributes);

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

    public function createInvoiceBatchRecord(): string
    {
        $batchId = $this->uuid();

        DB::table('invoice_batches')
            ->insert([
                'uuid' => $batchId,
                'created_at' => now()->toDateTimeString(),
            ]);

        return $batchId;
    }

    public function createBatchInvoices(int $count = 2, array $invoiceAttributes = []): string
    {
        $batchId = $this->createInvoiceBatchRecord();
        $attributes = array_merge([
            'batch_id' => $batchId,
            'user_uuid' => $this->user->uuid,
        ], $invoiceAttributes);

        /** @var Invoice $baseInvoice */
        $baseInvoice = Invoice::factory()
            ->make($attributes);
        $baseItems = InvoiceItem::factory()
            ->count($this->faker->numberBetween(1, 5))
            ->make();
        $baseScholarships = InvoiceScholarship::factory()
            ->count($this->faker->numberBetween(0, 3))
            ->make();
        $baseSchedules = InvoicePaymentSchedule::factory()
            ->count($this->faker->numberBetween(0, 3))
            ->make();
        $baseTerms = InvoicePaymentTerm::factory()
            ->count($this->faker->numberBetween(1, 5))
            ->make();

        Collection::times($count)
            ->map(function () use ($baseInvoice, $baseItems, $baseScholarships, $baseSchedules, $baseTerms) {
                $student = $this->createStudent();
                $invoice = $baseInvoice->replicate();
                $invoice->uuid = $this->uuid();
                $invoice->student_uuid = $student->uuid;
                $invoice->save();

                $baseItems->map(function (InvoiceItem $baseItem) use ($invoice) {
                    $item = $baseItem->replicate();
                    $item->uuid = $this->uuid();
                    $item->invoice_uuid = $invoice->uuid;

                    return $item;
                });

                $baseScholarships
                    ->each(function (InvoiceScholarship $base) use ($invoice) {
                        $scholarship = $base->replicate();
                        $scholarship->uuid = $this->uuid();
                        $scholarship->invoice_uuid = $invoice->uuid;
                        $scholarship->setAmount()->save();
                    });

                $baseSchedules
                    ->each(function (InvoicePaymentSchedule $baseSchedule) use ($invoice, $baseTerms) {
                        $schedule = $baseSchedule->replicate();
                        $schedule->uuid = $this->uuid();
                        $schedule->invoice_uuid = $invoice->uuid;

                        $terms = $baseTerms->map(function (InvoicePaymentTerm $baseTerm) use ($schedule) {
                            $term = $baseTerm->replicate();
                            $term->uuid = $this->uuid();
                            $term->invoice_uuid = $schedule->invoice_uuid;
                            $term->invoice_payment_schedule_uuid = $schedule->uuid;

                            return $term;
                        });

                        $schedule->setRelation('invoicePaymentTerms', $terms);
                        $schedule->setAmount()->save();

                        $terms->each(fn ($term) => $term->save());
                    });

                return $invoice;
            });

        return $batchId;
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function selectInvoice(Invoice $invoice): InvoiceSelection
    {
        return $this->user->invoiceSelections()
            ->create([
                'school_id' => $this->school->id,
                'invoice_uuid' => $invoice->uuid
            ]);
    }
}
