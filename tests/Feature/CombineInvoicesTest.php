<?php

namespace Tests\Feature;

use App\Jobs\SendNewInvoiceNotification;
use App\Models\Activity;
use App\Models\Invoice;
use App\Models\InvoicePaymentSchedule;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Inertia\Testing\Assert;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class CombineInvoicesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;

    protected bool $signIn = true;
    protected Collection $selection;

    protected function createSelection()
    {
        $invoice1 = $this->createInvoice();
        $invoice2 = $this->createInvoice();

        $this->selectInvoice($invoice1);
        $this->selectInvoice($invoice2);

        $this->selection = collect([$invoice1, $invoice2]);
    }

    public function test_need_permission_to_combine_invoices()
    {
        $this->get('/combine')
            ->assertForbidden();
    }

    public function test_cant_combine_without_a_selection()
    {
        $this->assignPermission('create', Invoice::class);

        $this->get('/combine')
            ->assertSessionHas('error')
            ->assertRedirect();
    }

    public function test_can_get_to_combine_page()
    {
        $this->assignPermission('create', Invoice::class);
        $this->createSelection();

        $this->get('/combine')
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('selection')
                ->has('endpoint')
                ->has('method')
                ->component('invoices/Combine')
            );
    }

    public function test_combining_validation()
    {
        $this->assignPermission('create', Invoice::class);

        $data = [
            'users' => [],
            'title' => null,
            'description' => $this->faker->sentence(),
            'available_at' => now()->format('Y-m-d\TH:i:s.v\Z'),
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => true,
            'payment_schedules' => [
                [
                    'id' => $this->uuid(),
                    'terms' => [
                        [
                            'id' => $this->uuid(),
                            'amount' => 'not a number',
                        ]
                    ],
                ]
            ],
        ];

        $this->post(route('invoices.combine'), $data)
            ->assertRedirect()
            ->assertSessionHasErrors([
                'users',
                'title',
                'payment_schedules.0.terms.0.amount',
            ]);
    }

    public function test_can_join_invoices_without_payment_schedules()
    {
        $this->withoutExceptionHandling();
        Queue::fake();

        $this->assignPermission('create', Invoice::class);
        $this->createSelection();
        $this->assertEquals($this->selection->count(), $this->user->invoiceSelections()->count());

        $addedUser1 = $this->createUser();
        $addedUser2 = $this->createUser();

        /** @var Term $term */
        $term = $this->school->terms()->save(
            Term::factory()->make()
        );

        $data = [
            'users' => [$addedUser1->id, $addedUser2->id],
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence(),
            'available_at' => now()->format('Y-m-d\TH:i:s.v\Z'),
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => $term->id,
            'notify' => true,
            'payment_schedules' => [],
        ];

        $this->post(route('invoices.combine'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(0, $this->user->invoiceSelections()->count());

        $childInvoices = Invoice::whereIn('uuid', $this->selection->pluck('uuid'))
            ->whereNotNull('parent_uuid')
            ->get();

        $this->assertEquals($this->selection->count(), $childInvoices->count());

        $invoice = Invoice::find($childInvoices->first()->parent_uuid);

        $this->assertEquals($data['title'], $invoice->title);
        $this->assertEquals($data['description'], $invoice->description);
        $this->assertEquals($data['term_id'], $invoice->term_id);
        $this->assertEquals(Carbon::parse($data['available_at'])->setTimezone(config('app.timezone'))->toDateTimeString(), $invoice->available_at->toDateTimeString());
        $this->assertEquals(Carbon::parse($data['due_at'])->setTimezone(config('app.timezone'))->toDateTimeString(), $invoice->due_at->toDateTimeString());
        $this->assertNotNull($invoice->invoice_date);
        $this->assertEquals(0, $invoice->invoicePaymentSchedules()->count());
        $this->assertEquals(1, $invoice->activities()->count());
        $this->assertTrue($invoice->is_parent);

        Queue::assertPushed(SendNewInvoiceNotification::class);
    }

    public function test_can_join_invoices_with_payment_schedules()
    {
        Queue::fake();

        $this->assignPermission('create', Invoice::class);
        $this->createSelection();
        $this->assertEquals($this->selection->count(), $this->user->invoiceSelections()->count());

        $addedUser1 = $this->createUser();

        /** @var Term $term */
        $term = $this->school->terms()->save(
            Term::factory()->make()
        );

        $data = [
            'users' => [$addedUser1->id],
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence(),
            'available_at' => now()->format('Y-m-d\TH:i:s.v\Z'),
            'due_at' => now()->addMonths(3)->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => $term->id,
            'notify' => false,
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

        $this->post(route('invoices.combine'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(0, $this->user->invoiceSelections()->count());

        $childInvoices = Invoice::whereIn('uuid', $this->selection->pluck('uuid'))
            ->whereNotNull('parent_uuid')
            ->get();

        $this->assertEquals($this->selection->count(), $childInvoices->count());

        $invoice = Invoice::find($childInvoices->first()->parent_uuid);

        $this->assertEquals($data['title'], $invoice->title);
        $this->assertEquals($data['description'], $invoice->description);
        $this->assertEquals($data['term_id'], $invoice->term_id);
        $this->assertEquals(Carbon::parse($data['available_at'])->setTimezone(config('app.timezone'))->toDateTimeString(), $invoice->available_at->toDateTimeString());
        $this->assertEquals(Carbon::parse($data['due_at'])->setTimezone(config('app.timezone'))->toDateTimeString(), $invoice->due_at->toDateTimeString());
        $this->assertNotNull($invoice->invoice_date);
        $this->assertNotNull($invoice->published_at);
        $this->assertEquals(1, $invoice->activities()->count());
        $this->assertEquals(count($data['payment_schedules']), $invoice->invoicePaymentSchedules()->count());
        $this->assertTrue($invoice->is_parent);

        foreach ($data['payment_schedules'] as $datum) {
            $amount = array_reduce(
                $datum['terms'],
                fn ($total, $term) => $total + $term['amount'],
                0
            );

            /** @var InvoicePaymentSchedule|null $schedule */
            $schedule = $invoice->invoicePaymentSchedules()
                ->firstWhere('amount', $amount);

            $this->assertNotNull($schedule);
            $this->assertEquals(count($datum['terms']), $schedule->invoicePaymentTerms()->count());

            foreach ($datum['terms'] as $term) {
                $this->assertTrue(
                    $schedule->invoicePaymentTerms()
                        ->where('amount_due', $term['amount'])
                        ->where('invoice_uuid', $invoice->uuid)
                        ->where('due_at', Carbon::parse($term['due_at'])->setTimezone(config('app.timezone'))->toDateTimeString())
                        ->exists()
                );
            }
        }

        Queue::assertNothingPushed();
    }

    public function test_can_join_invoices_with_a_voided_invoice()
    {
        $this->assignPermission('create', Invoice::class);
        $this->createSelection();
        $voided = $this->selection->random();
        $voided->update(['voided_at' => now()]);
        $this->assertEquals($this->selection->count(), $this->user->invoiceSelections()->count());

        $addedUser1 = $this->createUser();

        $data = [
            'users' => [$addedUser1->id],
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence(),
            'available_at' => now()->format('Y-m-d\TH:i:s.v\Z'),
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'payment_schedules' => [],
        ];

        $this->post(route('invoices.combine'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(0, $this->user->invoiceSelections()->count());

        $childInvoices = Invoice::whereIn('uuid', $this->selection->pluck('uuid'))
            ->whereNotNull('parent_uuid')
            ->get();

        $this->assertEquals($this->selection->count() - 1, $childInvoices->count());
    }

    public function test_can_combine_invoices_into_a_draft()
    {
        Queue::fake();
        $this->assignPermission('create', Invoice::class);
        $this->createSelection();
        $this->assertEquals($this->selection->count(), $this->user->invoiceSelections()->count());

        $addedUser1 = $this->createUser();
        $addedUser2 = $this->createUser();

        $data = [
            'draft' => true,
            'users' => [$addedUser1->id, $addedUser2->id],
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence(),
            'available_at' => null,
            'due_at' => null,
            'term_id' => null,
            'notify' => true,
            'payment_schedules' => [],
        ];

        $this->post(route('invoices.combine'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(0, $this->user->invoiceSelections()->count());

        $childInvoices = Invoice::whereIn('uuid', $this->selection->pluck('uuid'))
            ->whereNotNull('parent_uuid')
            ->get();

        $this->assertEquals($this->selection->count(), $childInvoices->count());
        $invoice = Invoice::find($childInvoices->first()->parent_uuid);
        $this->assertNull($invoice->published_at);
        $this->assertTrue($invoice->is_parent);

        Queue::assertNothingPushed();
    }

    public function test_can_update_draft_invoice()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);
        $this->assignPermission('update', Invoice::class);
        $this->createSelection();
        $this->assertEquals($this->selection->count(), $this->user->invoiceSelections()->count());

        $addedUser1 = $this->createUser();
        $addedUser2 = $this->createUser();

        // Create the initial invoice
        $data = [
            'draft' => true,
            'users' => [$addedUser1->id, $addedUser2->id],
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence(),
            'available_at' => now()->format('Y-m-d\TH:i:s.v\Z'),
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'payment_schedules' => [],
        ];

        $this->post(route('invoices.combine'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(0, $this->user->invoiceSelections()->count());

        $childInvoices = Invoice::whereIn('uuid', $this->selection->pluck('uuid'))
            ->whereNotNull('parent_uuid')
            ->get();

        $invoice = Invoice::find($childInvoices->first()->parent_uuid);
        $this->assertNull($invoice->published_at);

        // Update it with the same students but different users
        $data = [
            'draft' => true,
            'users' => [$addedUser2->id],
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence(),
            'available_at' => null,
            'due_at' => null,
            'term_id' => null,
            'notify' => false,
            'payment_schedules' => [],
        ];

        $this->put("/combine/{$invoice->uuid}", $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(1, $invoice->users()->count());
        $this->assertEquals($addedUser2->uuid, $invoice->users()->first()->uuid);

        $this->assertNull($invoice->available_at);
        $this->assertNull($invoice->due_at);
        $this->assertTrue(
            Invoice::whereIn('uuid', $this->selection->pluck('uuid'))
                ->where('parent_uuid', $invoice->uuid)
                ->exists()
        );
        $this->assertTrue($invoice->is_parent);

        $invoice->activities->each(function (Activity $activity) {
            $this->assertTrue(Str::contains($activity->description, [
                'Created as a draft',
                'updated the draft invoice',
            ]));
        });
    }

    public function test_can_update_draft_invoices_with_different_invoices_and_publish()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);
        $this->assignPermission('update', Invoice::class);
        $this->createSelection();
        $this->assertEquals($this->selection->count(), $this->user->invoiceSelections()->count());

        $addedUser1 = $this->createUser();

        // Create the initial invoice
        $data = [
            'draft' => true,
            'users' => [$addedUser1->id],
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence(),
            'available_at' => now()->format('Y-m-d\TH:i:s.v\Z'),
            'due_at' => now()->addMonth()->format('Y-m-d\TH:i:s.v\Z'),
            'term_id' => null,
            'notify' => false,
            'payment_schedules' => [],
        ];

        $this->post(route('invoices.combine'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(0, $this->user->invoiceSelections()->count());

        $childInvoices = Invoice::whereIn('uuid', $this->selection->pluck('uuid'))
            ->whereNotNull('parent_uuid')
            ->get();

        $invoice = Invoice::find($childInvoices->first()->parent_uuid);
        $this->assertNull($invoice->published_at);

        // Remove one of the child invoices
        $childInvoices->random()->update(['parent_uuid' => null]);

        // Update it with the same students but different users
        $data = [
            'draft' => false,
            'users' => [$addedUser1->id],
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence(),
            'available_at' => null,
            'due_at' => null,
            'term_id' => null,
            'notify' => false,
            'payment_schedules' => [],
        ];

        $this->get("/combine/{$invoice->uuid}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('invoice')
                ->has('assignedUsers')
                ->has('suggestedUsers')
                ->has('selection')
                ->has('endpoint')
                ->has('method')
            );

        // This is to ensure that the original creation date is preserved
        sleep(2);
        $this->put("/combine/{$invoice->uuid}", $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(1, $invoice->users()->count());
        $this->assertEquals($addedUser1->uuid, $invoice->users()->first()->uuid);

        $this->assertNull($invoice->available_at);
        $this->assertNull($invoice->due_at);
        $this->assertNotNull($invoice->published_at);
        $this->assertTrue(
            Invoice::whereIn('uuid', $this->selection->pluck('uuid'))
                ->where('parent_uuid', $invoice->uuid)
                ->exists()
        );
        $this->assertTrue($invoice->is_parent);
        $this->assertNotEquals($invoice->created_at->toDateTimeString(), $invoice->updated_at->toDateTimeString());

        $invoice->activities->each(function (Activity $activity) {
            $this->assertTrue(Str::contains($activity->description, [
                'Created as a draft',
                'updated and published the invoice',
            ]));
        });
    }
}
