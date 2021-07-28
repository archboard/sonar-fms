<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\Invoice;
use App\Models\Scholarship;
use App\Models\Student;
use App\ResolutionStrategies\Greatest;
use App\ResolutionStrategies\Least;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class UpdateInvoiceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use CreatesInvoice;

    protected Student $student;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->student = $this->school->students->random();
        $this->invoice = $this->createInvoice();
    }

    public function test_can_update_all_areas_of_existing_invoice()
    {
        $this->markTestIncomplete('Skipping update invoice test');

        ray()->clearScreen();

        $this->withoutExceptionHandling();
        $this->assignPermission('update', Invoice::class);

        /** @var Scholarship $scholarship */
        $scholarship = $this->school->scholarships()->save(
            Scholarship::factory()->make([
                'tenant_id' => $this->tenant->id,
                'amount' => 200,
                'percentage' => 50,
                'resolution_strategy' => Greatest::class,
            ])
        );
        /** @var Fee $fee */
        $fee = $this->school->fees()->save(
            Fee::factory()->make([
                'tenant_id' => $this->tenant->id,
                'amount' => 200,
            ])
        );

        $existingItems = $this->invoice->invoiceItems()->get();
        $existingScholarships = $this->invoice->invoiceScholarships()->get();

        $invoiceData = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $existingItems->first()->id,
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 2000,
                    'quantity' => 2,
                ],
                [
                    'id' => (string) Uuid::uuid4(),
                    'fee_id' => $fee->id,
                    'sync_with_fee' => true,
                    'name' => 'Line item 2',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
                [
                    'id' => (string) Uuid::uuid4(),
                    'fee_id' => $fee->id,
                    'sync_with_fee' => false,
                    'name' => 'Non syncing fee',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [
                [
                    'id' => $existingScholarships->first()->id,
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'sync_with_scholarship' => false,
                    'amount' => 500,
                    'percentage' => null,
                    'resolution_strategy' => Greatest::class,
                ],
                [
                    'id' => (string) Uuid::uuid4(),
                    'scholarship_id' => $scholarship->id,
                    'name' => 'Tuition Assistance B',
                    'sync_with_scholarship' => true,
                    'amount' => 100,
                    'percentage' => null,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => (string) Uuid::uuid4(),
                    'scholarship_id' => $scholarship->id,
                    'name' => 'Non syncing scholarship',
                    'sync_with_scholarship' => false,
                    'amount' => 100,
                    'percentage' => 10,
                    'resolution_strategy' => Least::class,
                ],
            ],
        ];

        $this->put(route('students.invoices.update', [$this->student, $this->invoice]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        // Refresh the model
        $this->invoice->unsetRelations();
        $this->invoice->refresh();

        $this->assertEquals($invoiceData['title'], $this->invoice->title);
        $this->assertEquals($invoiceData['description'], $this->invoice->description);
        $this->assertEquals(Carbon::parse($invoiceData['due_at'])->startOfSecond(), $this->invoice->due_at);

        $total = Invoice::getSubmittedItemsTotal(collect($invoiceData['items']), $this->school->fees->keyBy('id'));
        $this->assertEquals(4300, $total);
        $scholarship1 = 500;
        $scholarship2 = $total * .5;
        $scholarship3 = 100;

        $amountDue = $total - $scholarship1 - $scholarship2 - $scholarship3;
        $this->assertEquals($amountDue, $this->invoice->amount_due);
        $this->assertEquals($amountDue, $this->invoice->remaining_balance);

        $this->assertEquals(3, $this->invoice->invoiceItems()->count());
        foreach ($invoiceData['items'] as $item) {
            $data = Arr::except($item, 'id');

            if ($item['sync_with_fee']) {
                $data = Arr::except($data, ['name', 'amount_per_unit']);
                $data['name'] = $fee->name;
                $data['amount_per_unit'] = $fee->amount;
            }

            $this->assertDatabaseHas('invoice_items', $data);
        }
        $this->assertDatabaseMissing('invoice_items', ['id' => $existingItems->last()->id]);

        $this->assertEquals(3, $this->invoice->invoiceScholarships()->count());
        foreach ($invoiceData['scholarships'] as $item) {
            $data = Arr::except($item, 'id');

            if ($item['sync_with_scholarship']) {
                $replace = [
                    'name',
                    'amount',
                    'percentage',
                    'resolution_strategy',
                ];

                foreach ($replace as $key) {
                    $data[$key] = $scholarship->getAttribute($key);
                }
            }

            $this->assertDatabaseHas('invoice_scholarships', $data);
        }
        $this->assertDatabaseMissing('invoice_scholarships', ['id' => $existingScholarships->last()->id]);
    }
}
