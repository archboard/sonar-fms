<?php

namespace Tests\Feature;

use App\Jobs\SendNewInvoiceNotification;
use App\Models\Fee;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceScholarship;
use App\Models\Scholarship;
use App\Models\Term;
use App\ResolutionStrategies\Greatest;
use App\ResolutionStrategies\Least;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Queue;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CreateInvoiceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_cannot_create_without_permission()
    {
        $student = $this->school->students->random();

        $this->post(route('students.invoices.store', [$student]), [])
            ->assertForbidden();
    }

    public function test_can_create_invoice_for_student_with_notify_now()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        $term = $this->school->terms()->save(
            Term::factory()->make()
        );
        $invoice = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => $term->id,
            'notify' => true,
            'items' => [
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoice)
            ->assertRedirect()
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals($invoice['title'], $invoice->title);
        $this->assertEquals($invoice['description'], $invoice->description);
        $this->assertEquals($invoice['term_id'], $invoice->term_id);
        $this->assertEquals($invoice['notify'], $invoice->notify);
        $this->assertEquals(100, $invoice->amount_due);
        $this->assertEquals(100, $invoice->remaining_balance);
        $this->assertEquals(now()->addMonth()->startOfMinute(), $invoice->due_at->startOfMinute());
        $this->assertEquals(1, $invoice->invoiceItems()->count());

        Queue::assertPushed(SendNewInvoiceNotification::class);
    }

    public function test_can_create_invoice_for_student_without_notify_now()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        /** @var Fee $fee */
        $fee = $this->school->fees()->save(
            Fee::factory()->make(['tenant_id' => $this->tenant->id])
        );
        $invoice = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => $fee->id,
                    'sync_with_fee' => true,
                    'name' => 'Not matching name',
                    'amount_per_unit' => 100,
                    'quantity' => 2,
                ]
            ],
            'scholarships' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoice)
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertNotPushed(SendNewInvoiceNotification::class);

        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals($invoice['title'], $invoice->title);
        $this->assertEquals($invoice['description'], $invoice->description);
        $this->assertEquals($invoice['term_id'], $invoice->term_id);
        $this->assertEquals($invoice['notify'], $invoice->notify);
        $this->assertEquals(300, $invoice->amount_due);
        $this->assertEquals(300, $invoice->remaining_balance);
        $this->assertEquals(now()->addMonth()->startOfMinute(), $invoice->due_at->startOfMinute());
        $this->assertEquals(2, $invoice->invoiceItems()->count());

        // Test that the name and amount matches from the underlying fee, not the one we sent
        /** @var InvoiceItem $itemWithSync */
        $itemWithSync = $invoice->invoiceItems()
            ->firstWhere('fee_id', $fee->id);
        $this->assertEquals($fee->name, $itemWithSync->name);
        $this->assertEquals($fee->amount, $itemWithSync->amount_per_unit);
        $this->assertEquals($fee->amount * $itemWithSync->quantity, $itemWithSync->amount);
    }

    public function test_invoice_does_not_get_created_if_sql_fails_for_its_items()
    {
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        $invoice = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => true,
            'items' => [
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => $this->faker->paragraphs(10, true),
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoice)
            ->assertStatus(500);

        $this->assertEquals(0, $student->invoices()->count());

        Queue::assertNotPushed(SendNewInvoiceNotification::class);
    }

    public function test_can_create_invoice_with_scholarships()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        $invoice = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Not matching name',
                    'amount_per_unit' => 100,
                    'quantity' => 2,
                ]
            ],
            'scholarships' => [
                [
                    'id' => Uuid::uuid4(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'sync_with_scholarship' => false,
                    'amount' => 100,
                    'percentage' => null,
                    'resolution_strategy' => Least::class,
                ]
            ],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoice)
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertNotPushed(SendNewInvoiceNotification::class);

        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals($invoice['title'], $invoice->title);
        $this->assertEquals($invoice['description'], $invoice->description);
        $this->assertEquals($invoice['term_id'], $invoice->term_id);
        $this->assertEquals($invoice['notify'], $invoice->notify);
        $this->assertEquals(200, $invoice->amount_due);
        $this->assertEquals(200, $invoice->remaining_balance);
        $this->assertEquals(now()->addMonth()->startOfMinute(), optional($invoice->due_at)->startOfMinute());
        $this->assertEquals(2, $invoice->invoiceItems()->count());

        $this->assertEquals(1, $invoice->invoiceScholarships()->count());
        /** @var InvoiceScholarship $scholarship */
        $scholarship = $invoice->invoiceScholarships()->first();
        $this->assertEquals('Tuition Assistance A', $scholarship->name);
        $this->assertEquals(100, $scholarship->amount);
        $this->assertEquals(0, $scholarship->percentage);
    }

    public function test_can_create_invoice_with_scholarships_that_sync()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        /** @var Scholarship $scholarship */
        $scholarship = $this->school->scholarships()->save(
            Scholarship::factory()->make([
                'tenant_id' => $this->tenant->id,
                'amount' => 200,
            ])
        );
        $invoice = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 500,
                    'quantity' => 1,
                ],
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Not matching name',
                    'amount_per_unit' => 500,
                    'quantity' => 1,
                ]
            ],
            'scholarships' => [
                [
                    'id' => Uuid::uuid4(),
                    'scholarship_id' => $scholarship->id,
                    'name' => 'Tuition Assistance A',
                    'sync_with_scholarship' => true,
                    'amount' => null,
                    'percentage' => '12.5',
                    'resolution_strategy' => Least::class,
                ]
            ],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoice)
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertNotPushed(SendNewInvoiceNotification::class);

        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals(800, $invoice->amount_due);
        $this->assertEquals(800, $invoice->remaining_balance);
        $this->assertEquals(2, $invoice->invoiceItems()->count());

        $this->assertEquals(1, $invoice->invoiceScholarships()->count());
        /** @var InvoiceScholarship $invoiceScholarship */
        $invoiceScholarship = $invoice->invoiceScholarships()->first();
        $this->assertEquals($scholarship->name, $invoiceScholarship->name);
        $this->assertEquals($scholarship->amount, $invoiceScholarship->amount);
        $this->assertEquals(0, $invoiceScholarship->percentage);
    }

    public function test_can_create_invoice_with_a_single_item_and_multiple_scholarships()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        $invoiceData = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 1000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [
                [
                    'id' => Uuid::uuid4(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'sync_with_scholarship' => false,
                    'amount' => 100,
                    'percentage' => 50,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => Uuid::uuid4(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance B',
                    'sync_with_scholarship' => false,
                    'amount' => 100,
                    'percentage' => null,
                    'resolution_strategy' => Least::class,
                ],
            ],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertNotPushed(SendNewInvoiceNotification::class);

        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals(800, $invoice->amount_due);
        $this->assertEquals(800, $invoice->remaining_balance);
        $this->assertEquals(1, $invoice->invoiceItems()->count());
        $this->assertEquals(2, $invoice->invoiceScholarships()->count());

        foreach ($invoiceData['scholarships'] as $scholarship) {
            $row = Arr::except($scholarship, 'id');
            $row['invoice_uuid'] = $invoice->uuid;

            $this->assertDatabaseHas('invoice_scholarships', $row);
        }
    }

    public function test_can_create_invoice_with_multiple_scholarships_that_have_resolution_strategies()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        $invoiceData = [
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => Uuid::uuid4(),
                    'fee_id' => null,
                    'sync_with_fee' => false,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 1000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [
                [
                    'id' => Uuid::uuid4(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'sync_with_scholarship' => false,
                    'amount' => 500,
                    'percentage' => 10,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => Uuid::uuid4(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance B',
                    'sync_with_scholarship' => false,
                    'amount' => 100,
                    'percentage' => 75,
                    'resolution_strategy' => Greatest::class,
                ],
            ],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertNotPushed(SendNewInvoiceNotification::class);

        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals(150, $invoice->amount_due);
        $this->assertEquals(150, $invoice->remaining_balance);
        $this->assertEquals(1, $invoice->invoiceItems()->count());
        $this->assertEquals(2, $invoice->invoiceScholarships()->count());

        foreach ($invoiceData['scholarships'] as $scholarship) {
            $row = Arr::except($scholarship, 'id');
            $row['invoice_uuid'] = $invoice->uuid;

            $this->assertDatabaseHas('invoice_scholarships', $row);
        }
    }
}
