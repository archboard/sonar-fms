<?php

namespace Tests\Feature;

use App\Http\Requests\CreateInvoiceRequest;
use App\Jobs\SendNewInvoiceNotification;
use App\Jobs\SetStudentCachedValues;
use App\Models\Activity;
use App\Models\Fee;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use App\Models\InvoiceScholarship;
use App\Models\Student;
use App\Models\Term;
use App\ResolutionStrategies\Greatest;
use App\ResolutionStrategies\Least;
use App\Utilities\NumberUtility;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use GrantHolle\Timezone\Facades\Timezone;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class CreateInvoiceForStudentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use SignsIn;

    protected function getTestRequest($data = []): CreateInvoiceRequest
    {
        $request = new CreateInvoiceRequest([], $data);
        $request->setMethod('post');
        $request->setUserResolver(fn () => $this->user);

        return $request->setContainer($this->app)
            ->setRedirector($this->app->make(Redirector::class));
    }

    public function test_cannot_create_without_permission()
    {
        $student = $this->school->students->random();

        $this->post(route('students.invoices.store', [$student]), [])
            ->assertForbidden();
    }

    public function test_has_all_required_props()
    {
        $this->assignPermission('create', Invoice::class);

        $this->get(route('students.invoices.create', $this->school->students->random()))
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('students')
                ->has('endpoint')
                ->has('method')
                ->component('invoices/Create')
            );
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
        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => $term->id,
            'notify' => true,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertNotNull($invoice->invoice_number);
        $this->assertEquals($invoiceData['title'], $invoice->title);
        $this->assertEquals($invoiceData['title'], $invoice->raw_title);
        $this->assertEquals($invoiceData['description'], $invoice->description);
        $this->assertEquals($invoiceData['term_id'], $invoice->term_id);
        $this->assertEquals($invoiceData['notify'], $invoice->notify);
        $this->assertEquals(100, $invoice->amount_due);
        $this->assertEquals(100, $invoice->remaining_balance);
        $this->assertEquals(100, $invoice->subtotal);
        $this->assertEquals(0, $invoice->discount_total);
        $this->assertEquals(now()->addMonth()->startOfMinute(), optional($invoice->due_at)->startOfMinute());
        $this->assertEquals($invoiceData['available_at'], $invoice->available_at);
        $this->assertEquals(1, $invoice->invoiceItems()->count());
        $this->assertNotNull($invoice->invoice_date);
        ray()->queries();
        $this->assertEquals(1, $invoice->activities()->count());

        Queue::assertPushed(SendNewInvoiceNotification::class);
    }

    public function test_can_create_invoice_for_student_without_notify_now()
    {
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        /** @var Fee $fee */
        $fee = $this->school->fees()->save(
            Fee::factory()->make(['tenant_id' => $this->tenant->id])
        );
        $available = now()->startOfSecond();

        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'available_at' => $available->format('Y-m-d\TH:i:s.v\Z'),
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                    'random_key' => 'random value',
                ],
                [
                    'id' => $this->uuid(),
                    'fee_id' => $fee->id,
                    'name' => 'Not matching name',
                    'amount_per_unit' => 100,
                    'quantity' => 2,
                ]
            ],
            'scholarships' => [],
            'payment_schedules' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertNotPushed(SendNewInvoiceNotification::class);
        Queue::assertPushed(SetStudentCachedValues::class, function ($job) use ($student) {
            return $job->studentUuid === $student->uuid;
        });

        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals($invoiceData['title'], $invoice->title);
        $this->assertEquals($invoiceData['description'], $invoice->description);
        $this->assertEquals($invoiceData['term_id'], $invoice->term_id);
        $this->assertEquals($invoiceData['notify'], $invoice->notify);
        $this->assertEquals($invoiceData['notify'], $invoice->notify);
        $this->assertEquals(300, $invoice->amount_due);
        $this->assertEquals(300, $invoice->remaining_balance);
        $this->assertEquals(300, $invoice->subtotal);
        $this->assertEquals(0, $invoice->discount_total);
        $this->assertEquals(now()->addMonth()->startOfMinute(), optional($invoice->due_at)->startOfMinute());
        $this->assertEquals($available, $invoice->available_at);
        $this->assertEquals(2, $invoice->invoiceItems()->count());
    }

    public function test_title_gets_dynamically_generated_with_included_term()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        /** @var Student $student */
        $student = $this->school->students->random();
        /** @var Term $term */
        $term = $this->school->terms()->save(
            Term::factory()->make()
        );
        $invoiceData = [
            'students' => [$student->id],
            'title' => '{last_name}, {first_name} {term} {school_year} Invoice',
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => $term->id,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();

        $this->assertEquals(
            "{$student->last_name}, {$student->first_name} {$term->abbreviation} {$term->school_years} Invoice",
            $invoice->title
        );
    }

    public function test_invoice_does_not_get_created_if_sql_fails_for_its_items()
    {
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => true,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => $this->faker->paragraphs(10, true),
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
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
        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Not matching name',
                    'amount_per_unit' => 100,
                    'quantity' => 2,
                ]
            ],
            'scholarships' => [
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'amount' => 100,
                    'percentage' => null,
                    'resolution_strategy' => Least::class,
                ]
            ],
            'payment_schedules' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertNotPushed(SendNewInvoiceNotification::class);

        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals($invoiceData['title'], $invoice->title);
        $this->assertEquals($invoiceData['description'], $invoice->description);
        $this->assertEquals($invoiceData['term_id'], $invoice->term_id);
        $this->assertEquals($invoiceData['notify'], $invoice->notify);
        $this->assertEquals(200, $invoice->amount_due);
        $this->assertEquals(200, $invoice->remaining_balance);
        $this->assertEquals(300, $invoice->subtotal);
        $this->assertEquals(100, $invoice->discount_total);
        $this->assertEquals(now()->addMonth()->startOfMinute(), optional($invoice->due_at)->startOfMinute());
        $this->assertEquals(2, $invoice->invoiceItems()->count());

        $this->assertEquals(1, $invoice->invoiceScholarships()->count());
        /** @var InvoiceScholarship $scholarship */
        $scholarship = $invoice->invoiceScholarships()->first();
        $this->assertEquals('Tuition Assistance A', $scholarship->name);
        $this->assertEquals(100, $scholarship->amount);
        $this->assertEquals(0, $scholarship->percentage);
    }

    public function test_can_create_invoice_with_scholarships_that_apply_to_certain_items_only()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();

        $item1Id = $this->uuid();
        $item2id = $this->uuid();
        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $item1Id,
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 1000,
                    'quantity' => 1,
                ],
                [
                    'id' => $item2id,
                    'fee_id' => null,
                    'name' => 'Not matching name',
                    'amount_per_unit' => 100,
                    'quantity' => 2,
                ]
            ],
            'scholarships' => [
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'amount' => 100,
                    'percentage' => null,
                    'resolution_strategy' => Least::class,
                    'applies_to' => [$item1Id]
                ]
            ],
            'payment_schedules' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertNotPushed(SendNewInvoiceNotification::class);

        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertEquals($invoiceData['title'], $invoice->title);
        $this->assertEquals($invoiceData['description'], $invoice->description);
        $this->assertEquals($invoiceData['term_id'], $invoice->term_id);
        $this->assertEquals($invoiceData['notify'], $invoice->notify);
        $this->assertEquals(1100, $invoice->amount_due);
        $this->assertEquals(1100, $invoice->remaining_balance);
        $this->assertEquals(1200, $invoice->subtotal);
        $this->assertEquals(100, $invoice->discount_total);
        $this->assertEquals(now()->addMonth()->startOfMinute(), optional($invoice->due_at)->startOfMinute());
        $this->assertEquals(2, $invoice->invoiceItems()->count());

        $this->assertEquals(1, $invoice->invoiceScholarships()->count());

        /** @var InvoiceScholarship $scholarship */
        $scholarship = $invoice->invoiceScholarships()->first();
        $this->assertEquals('Tuition Assistance A', $scholarship->name);
        $this->assertEquals(100, $scholarship->amount);
        $this->assertEquals(0, $scholarship->percentage);
        $this->assertEquals(1, $scholarship->appliesTo->count());

        $item = $scholarship->appliesTo->first();
        $this->assertEquals(
            $invoice->invoiceItems->first()->uuid,
            $item->uuid
        );
    }

    public function test_can_create_invoice_with_a_single_item_and_multiple_scholarships()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 1000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'amount' => 100,
                    'percentage' => 50,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance B',
                    'amount' => 100,
                    'percentage' => null,
                    'resolution_strategy' => Least::class,
                ],
            ],
            'payment_schedules' => [],
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
        $this->assertEquals(1000, $invoice->subtotal);
        $this->assertEquals(200, $invoice->discount_total);
        $this->assertEquals(1, $invoice->invoiceItems()->count());
        $this->assertEquals(2, $invoice->invoiceScholarships()->count());

        foreach ($invoiceData['scholarships'] as $scholarship) {
            $row = Arr::except($scholarship, 'id');
            $row['invoice_uuid'] = $invoice->uuid;
            $row['percentage'] = NumberUtility::convertPercentageFromUser($row['percentage']);

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
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 1000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'amount' => 500,
                    'percentage' => 10,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance B',
                    'amount' => 100,
                    'percentage' => 75,
                    'resolution_strategy' => Greatest::class,
                ],
            ],
            'payment_schedules' => [],
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
        $this->assertEquals(1000, $invoice->subtotal);
        $this->assertEquals(850, $invoice->discount_total);
        $this->assertEquals(1, $invoice->invoiceItems()->count());
        $this->assertEquals(2, $invoice->invoiceScholarships()->count());

        foreach ($invoiceData['scholarships'] as $scholarship) {
            $row = Arr::except($scholarship, 'id');
            $row['invoice_uuid'] = $invoice->uuid;
            $row['percentage'] = NumberUtility::convertPercentageFromUser($row['percentage']);

            $this->assertDatabaseHas('invoice_scholarships', $row);
        }
    }

    public function test_can_create_invoice_with_a_payment_schedule()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();
        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 100,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [
                [

                    'id' => $this->uuid(),
                    'terms' => [
                        [
                            'id' => $this->uuid(),
                            'amount' => 110,
                            'due_at' => now()->addMonths()->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                        [
                            'id' => $this->uuid(),
                            'amount' => 110,
                            'due_at' => now()->addMonths(2)->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                    ],
                ],
            ],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertNotNull($invoice->batch_id);
        $this->assertEquals($invoiceData['title'], $invoice->title);
        $this->assertEquals($invoiceData['description'], $invoice->description);
        $this->assertEquals($invoiceData['term_id'], $invoice->term_id);
        $this->assertEquals($invoiceData['notify'], $invoice->notify);
        $this->assertEquals(100, $invoice->amount_due);
        $this->assertEquals(100, $invoice->remaining_balance);
        $this->assertEquals(100, $invoice->subtotal);
        $this->assertEquals(0, $invoice->discount_total);
        $this->assertEquals(now()->addMonth()->startOfMinute(), optional($invoice->due_at)->startOfMinute());
        $this->assertEquals($invoiceData['available_at'], $invoice->available_at);
        $this->assertEquals(1, $invoice->invoiceItems()->count());
        $this->assertNotNull($invoice->invoice_date);

        $this->assertEquals(1, $invoice->invoicePaymentSchedules->count());
        $this->assertEquals(2, $invoice->invoicePaymentTerms->count());

        /** @var InvoicePaymentSchedule $schedule */
        $schedule = $invoice->invoicePaymentSchedules->first();
        $this->assertEquals(220, $schedule->amount);
        $this->assertEquals($invoice->batch_id, $schedule->batch_id);

        $schedule->invoicePaymentTerms->each(function (InvoicePaymentTerm $term, $index) use ($invoice) {
            $this->assertEquals(now()->addMonths($index + 1)->startOfMinute(), $term->due_at->startOfMinute());
            $this->assertEquals(110, $term->amount);
            $this->assertEquals($invoice->batch_id, $term->batch_id);
        });

        Queue::assertNotPushed(SendNewInvoiceNotification::class);
    }

    public function test_can_create_invoice_with_all_the_fixins()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        Queue::fake();
        $student = $this->school->students->random();

        $item1 = $this->uuid();
        $item2 = $this->uuid();
        $item3 = $this->uuid();
        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $item1,
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
                [
                    'id' => $item2,
                    'fee_id' => null,
                    'name' => 'Line item 2',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
                [
                    'id' => $item3,
                    'fee_id' => null,
                    'name' => 'Line item 3',
                    'amount_per_unit' => 5000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'amount' => null,
                    'percentage' => 10,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance B',
                    'amount' => 1000,
                    'percentage' => null,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance C',
                    'amount' => 100,
                    'percentage' => 50,
                    'resolution_strategy' => Greatest::class,
                    'applies_to' => [$item1, $item2],
                ],
            ],
            'payment_schedules' => [
                [

                    'id' => $this->uuid(),
                    'terms' => [
                        [
                            'id' => $this->uuid(),
                            'amount' => 6000,
                            'due_at' => now()->addMonths()->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                        [
                            'id' => $this->uuid(),
                            'amount' => 6000,
                            'due_at' => now()->addMonths(2)->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                    ],
                ],
                [

                    'id' => $this->uuid(),
                    'terms' => [
                        [
                            'id' => $this->uuid(),
                            'amount' => 5000,
                            'due_at' => now()->addMonths()->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                        [
                            'id' => $this->uuid(),
                            'amount' => 5000,
                            'due_at' => now()->addMonths(2)->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                        [
                            'id' => $this->uuid(),
                            'amount' => 5000,
                            'due_at' => now()->addMonths(3)->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                    ],
                ],
            ],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertNotNull($invoice->batch_id);
        $this->assertEquals($invoiceData['title'], $invoice->title);
        $this->assertEquals($invoiceData['description'], $invoice->description);
        $this->assertEquals($invoiceData['term_id'], $invoice->term_id);
        $this->assertEquals($invoiceData['notify'], $invoice->notify);
        $this->assertNotNull($invoice->published_at);
        $this->assertEquals(11500, $invoice->amount_due);
        $this->assertEquals(11500, $invoice->remaining_balance);
        $this->assertEquals(25000, $invoice->subtotal);
        $this->assertEquals(13500, $invoice->discount_total);
        $this->assertEquals(3, $invoice->invoiceItems()->count());
        $this->assertEquals(3, $invoice->invoiceScholarships()->count());
        $this->assertEquals(2, $invoice->invoicePaymentSchedules()->count());
        $this->assertEquals(5, $invoice->invoicePaymentTerms()->count());

        foreach ($invoiceData['payment_schedules'] as $schedule) {
            $this->assertDatabaseHas(
                'invoice_payment_schedules',
                [
                    'invoice_uuid' => $invoice->uuid,
                    'amount' => array_reduce($schedule['terms'], fn (int $total, array $item) => $total + $item['amount'], 0)
                ]
            );
        }

        $this->assertNotNull($invoice->created_at);
        $this->assertNotNull($invoice->updated_at);

        $invoice->invoiceItems->each(function (InvoiceItem $item) {
            $this->assertNotNull($item->created_at);
            $this->assertNotNull($item->updated_at);
        });

        $invoice->invoiceScholarships->each(function (InvoiceScholarship $scholarship) {
            $this->assertNotNull($scholarship->created_at);
            $this->assertNotNull($scholarship->updated_at);
        });

        $invoice->invoicePaymentSchedules->each(function (InvoicePaymentSchedule $schedule) {
            $this->assertNotNull($schedule->created_at);
            $this->assertNotNull($schedule->updated_at);

            $schedule->invoicePaymentTerms->each(function (InvoicePaymentTerm $term) {
                $this->assertNotNull($term->created_at);
                $this->assertNotNull($term->updated_at);
            });
        });

        Queue::assertNotPushed(SendNewInvoiceNotification::class);
    }

    public function test_full_field_validation()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);
        $this->school->update([
            'collect_tax' => true,
            'tax_rate' => 0.05,
            'tax_label' => 'Taxes',
        ]);
        $this->user->school->refresh();

        $item1 = $this->uuid();
        $item2 = $this->uuid();
        $item3 = $this->uuid();
        $invoiceData = [
            'students' => [1],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $item1,
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
                [
                    'id' => $item2,
                    'fee_id' => null,
                    'name' => 'Line item 2',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
                [
                    'id' => $item3,
                    'fee_id' => null,
                    'name' => 'Line item 3',
                    'amount_per_unit' => 5000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'amount' => null,
                    'percentage' => 10,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance B',
                    'amount' => 1000,
                    'percentage' => null,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance C',
                    'amount' => 100,
                    'percentage' => 50,
                    'resolution_strategy' => Greatest::class,
                    'applies_to' => [$item1, $item2],
                ],
            ],
            'payment_schedules' => [
                [

                    'id' => $this->uuid(),
                    'terms' => [
                        [
                            'id' => $this->uuid(),
                            'amount' => 6000,
                            'due_at' => now()->addMonths()->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                        [
                            'id' => $this->uuid(),
                            'amount' => 6000,
                            'due_at' => now()->addMonths(2)->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                    ],
                ],
                [

                    'id' => $this->uuid(),
                    'terms' => [
                        [
                            'id' => $this->uuid(),
                            'amount' => 5000,
                            'due_at' => now()->addMonths()->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                        [
                            'id' => $this->uuid(),
                            'amount' => 5000,
                            'due_at' => now()->addMonths(2)->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                        [
                            'id' => $this->uuid(),
                            'amount' => 5000,
                            'due_at' => now()->addMonths(3)->format('Y-m-d\TH:i:s.v\Z'),
                        ],
                    ],
                ],
            ],
            'apply_tax' => true,
            'use_school_tax_defaults' => false,
            'tax_rate' => 0.1,
            'tax_label' => 'VAT',
            'apply_tax_to_all_items' => false,
            'tax_items' => [
                [
                    'item_id' => $item1,
                    'selected' => true,
                    'tax_rate' => 8,
                ],
                [
                    'item_id' => $item3,
                    'selected' => true,
                    'tax_rate' => 9,
                ]
            ],
        ];

        $request = $this->getTestRequest($invoiceData);
        $request->validateResolved();

        $this->assertIsArray($request->validated());
    }

    public function test_tax_field_validation()
    {
        $this->expectException(ValidationException::class);
        $this->assignPermission('create', Invoice::class);
        $this->school->update([
            'collect_tax' => true,
            'tax_rate' => 0.05,
            'tax_label' => 'Taxes',
        ]);
        $this->user->school->refresh();

        $invoiceData = [
            'students' => [1],
            'title' => 'Test invoice 2021',
            'description' => null,
            'available_at' => null,
            'due_at' => null,
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => null,
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [],
            'apply_tax' => true,
            'use_school_tax_defaults' => false,
            'tax_rate' => null,
            'tax_label' => 'VAT',
        ];

        $this->getTestRequest($invoiceData)
            ->validateResolved();
    }

    public function test_tax_items_field_validation()
    {
        $this->assignPermission('create', Invoice::class);
        $this->school->update([
            'collect_tax' => true,
            'tax_rate' => 0.05,
            'tax_label' => 'Taxes',
        ]);
        $this->user->school->refresh();
        $student = $this->school->students->random();

        $invoiceData = [
            'students' => [1],
            'title' => 'Test invoice 2021',
            'description' => null,
            'available_at' => null,
            'due_at' => null,
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => null,
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [],
            'apply_tax' => true,
            'use_school_tax_defaults' => true,
            'tax_rate' => null,
            'tax_label' => null,
            'apply_tax_to_all_items' => false,
            'tax_items' => [],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertSessionHasErrors(['tax_items'])
            ->assertSessionDoesntHaveErrors(['tax_rate', 'tax_label']);
    }

    public function test_can_create_invoice_with_taxes()
    {
        Bus::fake();
        $this->assignPermission('create', Invoice::class);
        $this->school->update([
            'collect_tax' => true,
            'tax_rate' => 0.05,
            'tax_label' => 'Taxes',
        ]);
        $this->user->school->refresh();

        $student = $this->school->students->random();

        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => null,
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [],
            'apply_tax' => true,
            'use_school_tax_defaults' => true,
            'apply_tax_to_all_items' => true,
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();

        $this->assertEquals('Taxes', $invoice->tax_label);
        $this->assertEquals(.05, $invoice->tax_rate);
        $this->assertEquals(500, $invoice->tax_due);
        $this->assertEquals(10000, $invoice->pre_tax_subtotal);
        $this->assertEquals(10500, $invoice->amount_due);
        $this->assertEquals(10500, $invoice->remaining_balance);
        $this->assertEquals(10000, $invoice->subtotal);
        $this->assertEquals(0, $invoice->discount_total);
    }

    public function test_can_create_invoice_ignoring_taxes()
    {
        Bus::fake();
        $this->assignPermission('create', Invoice::class);
        $this->school->update([
            'collect_tax' => true,
            'tax_rate' => 0.05,
            'tax_label' => 'Taxes',
        ]);
        $this->user->school->refresh();

        $student = $this->school->students->random();

        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => null,
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [],
            'apply_tax' => false,
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();

        $this->assertNull($invoice->tax_label);
        $this->assertEquals(0, $invoice->tax_rate);
        $this->assertEquals(0, $invoice->tax_due);
        $this->assertEquals(10000, $invoice->pre_tax_subtotal);
        $this->assertEquals(10000, $invoice->amount_due);
        $this->assertEquals(10000, $invoice->remaining_balance);
        $this->assertEquals(10000, $invoice->subtotal);
        $this->assertEquals(0, $invoice->discount_total);
    }

    public function test_can_create_invoice_with_taxes_overriding_defaults()
    {
        Bus::fake();
        $this->assignPermission('create', Invoice::class);
        $this->school->update([
            'collect_tax' => true,
            'tax_rate' => 0.05,
            'tax_label' => 'Taxes',
        ]);
        $this->user->school->refresh();

        $student = $this->school->students->random();

        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'invoice_date' => '2021-08-11T06:17:20.933Z',
            'available_at' => '2021-08-11T00:00:00.000Z',
            'due_at' => null,
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [],
            'apply_tax' => true,
            'use_school_tax_defaults' => false,
            'tax_rate' => .01,
            'tax_label' => 'VAT',
            'apply_tax_to_all_items' => true,
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();

        $this->assertEquals('VAT', $invoice->tax_label);
        $this->assertEquals(.01, $invoice->tax_rate);
        $this->assertEquals(100, $invoice->tax_due);
        $this->assertEquals(10000, $invoice->pre_tax_subtotal);
        $this->assertEquals(10100, $invoice->amount_due);
        $this->assertEquals(10100, $invoice->remaining_balance);
        $this->assertEquals(10000, $invoice->subtotal);
        $this->assertEquals(0, $invoice->discount_total);
        $this->assertEquals(
            Carbon::parse('2021-08-11T06:17:20.933Z')->setTimezone($this->user->timezone)->format('Y-m-d'),
            $invoice->invoice_date->format('Y-m-d')
        );
    }

    public function test_can_save_invoice_as_draft()
    {
        Bus::fake();
        $this->assignPermission('create', Invoice::class);

        $students = $this->school->students->random(3);

        $invoiceData = [
            'students' => $students->pluck('id')->toArray(),
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => null,
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $this->uuid(),
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [],
            'payment_schedules' => [],
        ];

        $this->post(route('invoices.store.draft'), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $invoices = $this->school->invoices()->get();

        $this->assertEquals(3, $invoices->count());
        $this->assertTrue($invoices->every(fn (Invoice $invoice) => is_null($invoice->published_at)));
    }

    public function test_can_calculate_correct_tax_amount()
    {
        Bus::fake();

        $this->assignPermission('create', Invoice::class);
        $this->school->update([
            'collect_tax' => true,
            'tax_rate' => 0.05,
            'tax_label' => 'Taxes',
        ]);

        $student = $this->school->students->random();

        $item1 = $this->uuid();
        $item2 = $this->uuid();
        $item3 = $this->uuid();
        $invoiceData = [
            'students' => [$student->id],
            'title' => 'Test invoice 2021',
            'description' => $this->faker->sentence,
            'available_at' => null,
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'items' => [
                [
                    'id' => $item1,
                    'fee_id' => null,
                    'name' => 'Line item 1',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
                [
                    'id' => $item2,
                    'fee_id' => null,
                    'name' => 'Line item 2',
                    'amount_per_unit' => 10000,
                    'quantity' => 1,
                ],
                [
                    'id' => $item3,
                    'fee_id' => null,
                    'name' => 'Line item 3',
                    'amount_per_unit' => 5000,
                    'quantity' => 1,
                ],
            ],
            'scholarships' => [
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance A',
                    'amount' => null,
                    'percentage' => 10,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance B',
                    'amount' => 1000,
                    'percentage' => null,
                    'resolution_strategy' => Least::class,
                ],
                [
                    'id' => $this->uuid(),
                    'scholarship_id' => null,
                    'name' => 'Tuition Assistance C',
                    'amount' => 100,
                    'percentage' => 50,
                    'resolution_strategy' => Greatest::class,
                    'applies_to' => [$item1, $item2],
                ],
            ],
            'payment_schedules' => [],
            'apply_tax' => true,
            'use_school_tax_defaults' => true,
            'apply_tax_to_all_items' => false,
            'tax_items' => [
                [
                    'item_id' => $item1,
                    'selected' => true,
                    'tax_rate' => 8,
                ],
                [
                    'item_id' => $item2,
                    'selected' => false,
                    'tax_rate' => 8,
                ],
                [
                    'item_id' => $item3,
                    'selected' => true,
                    'tax_rate' => 9,
                ]
            ],
        ];

        $this->post(route('students.invoices.store', [$student]), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals(1, $student->invoices()->count());

        /** @var Invoice $invoice */
        $invoice = $student->invoices()->first();
        $this->assertTrue($invoice->apply_tax);
        $this->assertTrue($invoice->use_school_tax_defaults);
        $this->assertFalse($invoice->apply_tax_to_all_items);
        $this->assertEquals(2, $invoice->invoiceTaxItems()->count());
        $this->assertEquals(25000, $invoice->subtotal);
        $this->assertEquals(11500, $invoice->pre_tax_subtotal);
        $this->assertEquals(675, $invoice->tax_due);
        $this->assertEquals(13500, $invoice->discount_total);
        $this->assertEquals(12175, $invoice->amount_due);
        $this->assertEquals(12175, $invoice->remaining_balance);
        $this->assertEquals(0.05544148, $invoice->relative_tax_rate);
    }
}
