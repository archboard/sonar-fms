<?php

namespace Tests\Feature;

use App\Jobs\SendNewInvoiceNotification;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePaymentSchedule;
use App\Models\InvoicePaymentTerm;
use App\Models\InvoiceScholarship;
use App\Models\Student;
use App\ResolutionStrategies\Greatest;
use App\ResolutionStrategies\Least;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\Assert;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class StudentSelectionInvoiceCreationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use SignsIn;

    protected function makeSelection($count = 3)
    {
        $students = $this->school->students
            ->random($count);
        $selection = $students->map(fn (Student $student) => [
                'user_uuid' => $this->user->id,
                'student_uuid' => $student->id,
                'school_id' => $this->school->id,
            ]);

        DB::table('student_selections')->insert($selection->toArray());

        return $students;
    }

    public function test_cant_see_invoice_form_without_permission()
    {
        $this->get(route('selection.invoices.create'))
            ->assertForbidden();
    }

    public function test_can_see_invoice_form_with_permission()
    {
        $this->assignPermission('create', Invoice::class);

        $this->get(route('selection.invoices.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('students')
                ->has('endpoint')
                ->has('method')
            )
            ->assertOk();
    }

    public function test_can_create_invoices_for_selection()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);
        Queue::fake();

        $students = $this->makeSelection();

        $item1 = $this->uuid();
        $item2 = $this->uuid();
        $item3 = $this->uuid();
        $invoiceData = [
            'students' => $students->pluck('id')->toArray(),
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

        $this->post(route('selection.invoices.store'), $invoiceData)
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertNotPushed(SendNewInvoiceNotification::class);

        foreach ($students as $student) {
            $student->refresh();
            $this->assertEquals(1, $student->invoices()->count());

            /** @var Invoice $invoice */
            $invoice = $student->invoices()->first();
            $this->assertNotNull($invoice->batch_id);
            $this->assertEquals($invoiceData['title'], $invoice->title);
            $this->assertEquals($invoiceData['description'], $invoice->description);
            $this->assertEquals($invoiceData['term_id'], $invoice->term_id);
            $this->assertEquals($invoiceData['notify'], $invoice->notify);
            $this->assertEquals(11500, $invoice->amount_due);
            $this->assertEquals(11500, $invoice->remaining_balance);
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
        }
    }
}
