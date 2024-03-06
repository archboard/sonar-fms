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
use Carbon\Carbon;
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
                    ])->toArray(),
        ];
    }

    protected function createInvoice(array $invoiceAttributes = [], ?int $paymentSchedules = null): Invoice
    {
        $defaultAttributes = [
            'user_uuid' => $this->user->uuid,
            'batch_id' => $this->createInvoiceBatchRecord(),
        ];
        $attributes = array_merge($defaultAttributes, $invoiceAttributes);

        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create($attributes);

        $description = $invoice->published_at
            ? 'Created by :user.'
            : 'Created as a draft by :user.';
        activity()
            ->on($invoice)
            ->log($description);

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

        $invoice->setCalculatedAttributes(true)
            ->refresh();

        $this->seedPaymentSchedules($invoice, $paymentSchedules ?? $this->faker->numberBetween(0, 3));

        return $invoice;
    }

    protected function seedPaymentSchedules(Invoice $invoice, int $schedules = 2): Invoice
    {
        $invoice->invoicePaymentSchedules()->delete();

        InvoicePaymentSchedule::factory()
            ->count($schedules)
            ->make(['invoice_uuid' => $invoice->uuid])
            ->each(function (InvoicePaymentSchedule $schedule) use ($invoice) {
                $termCount = $this->faker->numberBetween(2, 5);
                $amountDue = (int) ceil($invoice->amount_due / $termCount);
                $terms = InvoicePaymentTerm::factory()
                    ->count($termCount)
                    ->make([
                        'amount' => $amountDue,
                        'amount_due' => $amountDue,
                        'remaining_balance' => $amountDue,
                        'invoice_uuid' => $schedule->invoice_uuid,
                        'invoice_payment_schedule_uuid' => $schedule->uuid,
                    ]);

                $schedule->setRelation('invoicePaymentTerms', $terms);
                $schedule->setAmount()->save();

                $terms->each(fn ($term) => $term->save());
            });

        return $invoice;
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
                'invoice_uuid' => $invoice->uuid,
            ]);
    }

    protected function createCombinedInvoice(int $count = 3): Invoice
    {
        $parentInvoice = $this->createInvoice([
            'student_uuid' => null,
            'is_parent' => true,
        ]);

        $children = Collection::times($count)
            ->map(fn () => $this->createInvoice(['parent_uuid' => $parentInvoice->uuid]));

        // Parent invoices don't have items or scholarships,
        // but they do have payment schedules
        $parentInvoice->invoiceItems()->delete();
        $parentInvoice->invoiceScholarships()->delete();
        $parentInvoice->setCalculatedAttributes(true);
        $parentInvoice->unsetRelations()
            ->refresh();
        $this->seedPaymentSchedules($parentInvoice);

        // Make sure everything reconciles correctly
        $this->assertEquals($children->sum('amount_due'), $parentInvoice->amount_due);
        $this->assertEquals($children->sum('remaining_balance'), $parentInvoice->remaining_balance);
        $this->assertEquals($children->sum('pre_tax_subtotal'), $parentInvoice->pre_tax_subtotal);
        $this->assertEquals($children->sum('tax_due'), $parentInvoice->tax_due);
        $this->assertEquals($children->sum('subtotal'), $parentInvoice->subtotal);
        $this->assertEquals($children->sum('discount_total'), $parentInvoice->discount_total);
        $this->assertEquals($children->sum('total_paid'), $parentInvoice->total_paid);

        return $parentInvoice->unsetRelations()
            ->refresh();
    }

    protected function getDateForInvoice(Carbon $date): string
    {
        return $date->format('Y-m-d\TH:i:s.v\Z');
    }
}
