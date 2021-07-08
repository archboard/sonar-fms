<?php

namespace Tests\Feature;

use App\Events\InvoiceImportFinished;
use App\Jobs\ProcessInvoiceImport;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceImport;
use App\Models\InvoiceItem;
use App\Models\InvoiceScholarship;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\ArrayShape;
use Tests\TestCase;

class InvoiceImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    #[ArrayShape(['id' => "string", 'column' => "null|string", 'value' => "null|string", 'isManual' => "bool"])]
    protected function makeMapField(string $column = null, string $value = null, bool $isManual = false): array
    {
        return [
            'id' => $this->uuid(),
            'column' => $column,
            'value' => $value,
            'isManual' => $isManual,
        ];
    }

    protected function getUploadedFile(string $fileName = 'sonar-import.xlsx'): UploadedFile
    {
        return new UploadedFile(base_path("tests/{$fileName}"), $fileName, null, null, true);
    }

    public function test_cannot_access_imports_without_permission()
    {
        $this->get(route('invoices.imports.index'))
            ->assertForbidden();
    }

    public function test_can_access_import_with_permission()
    {
        $this->assignPermission('viewAny', InvoiceImport::class);

        $this->get(route('invoices.imports.index'))
            ->assertOk();
    }

    public function test_can_create_invoice_import()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', InvoiceImport::class);
        Storage::fake();

        $data = [
            'files' => [[
                'file' => $this->getUploadedFile('sonar-import.xls'),
            ]],
            'heading_row' => 1,
            'starting_row' => 2,
        ];

        $this->post(route('invoices.imports.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(1, $this->school->invoiceImports()->count());
        $this->assertEquals(1, $this->user->invoiceImports()->count());

        /** @var InvoiceImport $import */
        $import = $this->user->invoiceImports()->first();
        Storage::assertExists($import->file_path);
        $this->assertEquals(1, $import->heading_row);
        $this->assertEquals(2, $import->starting_row);
        $this->assertEquals(3, $import->total_records);
        $this->assertEquals(0, $import->imported_records);
        $this->assertEquals(0, $import->failed_records);
    }

    public function test_can_view_edit_import_page()
    {
        $this->assignPermission('update', InvoiceImport::class);
        $import = InvoiceImport::create([
            'user_id' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => '/tmp/file.csv',
        ]);

        $this->get(route('invoices.imports.edit', $import))
            ->assertOk();
    }

    public function test_can_update_existing_import_with_different_file()
    {
        $this->assignPermission('update', InvoiceImport::class);
        Storage::fake();

        $originalPath = InvoiceImport::storeFile($this->getUploadedFile('sonar-import.xls'), $this->school);
        $import = InvoiceImport::create([
            'user_id' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => $originalPath,
        ]);

        $data = [
            'files' => [[
                'file' => $this->getUploadedFile(),
            ]],
            'heading_row' => 2,
            'starting_row' => 3,
        ];

        $this->put(route('invoices.imports.update', $import), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $import->refresh();
        $this->assertEquals('sonar-import.xlsx', $import->file_name);
        $this->assertEquals(2, $import->heading_row);
        $this->assertEquals(3, $import->starting_row);
        $this->assertEquals(3, $import->total_records);
        Storage::assertExists($import->file_path);
        Storage::assertMissing($originalPath);
        Storage::assertMissing(dirname($originalPath));
    }

    public function test_can_update_existing_import_with_same_file()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('update', InvoiceImport::class);
        Storage::fake();

        $originalPath = InvoiceImport::storeFile(UploadedFile::fake()->create('original.xlsx', 2), $this->school);
        $import = InvoiceImport::create([
            'user_id' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => $originalPath,
        ]);

        $data = [
            'files' => [[
                'file' => null,
                'existing' => true,
            ]],
            'heading_row' => 2,
            'starting_row' => 3,
        ];

        $this->put(route('invoices.imports.update', $import), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $import->refresh();
        $this->assertEquals('original.xlsx', $import->file_name);
        $this->assertEquals(2, $import->heading_row);
        $this->assertEquals(3, $import->starting_row);
        Storage::assertExists($import->file_path);
    }

    public function test_can_save_import_mapping_passing_validation()
    {
        $this->assignPermission('update', InvoiceImport::class);
        Storage::fake();

        $originalPath = InvoiceImport::storeFile(
            $this->getUploadedFile(),
            $this->school
        );
        $import = InvoiceImport::create([
            'user_id' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => $originalPath,
            'heading_row' => 2,
            'starting_row' => 3,
        ]);

        $data = [
            'student_attribute' => 'student_number',
            'student_column' => 'student number',
            'title' => $this->makeMapField(value: 'Invoice title', isManual: true),
            'description' => $this->makeMapField(),
            'due_at' => $this->makeMapField('due date'),
            'available_at' => $this->makeMapField('available date'),
            'term_id' => $this->makeMapField(),
            'notify' => false,
            'items' => [
                [
                    'fee_id' => $this->makeMapField(),
                    'name' => $this->makeMapField('invoice name'),
                    'amount_per_unit' => $this->makeMapField('invoice amount'),
                    'quantity' => $this->makeMapField(value: 1, isManual: true),
                ],
            ],
            'scholarships' => [
                [
                    'name' => $this->makeMapField(value: 'Assistance', isManual: true),
                    'use_amount' => false,
                    'amount' => $this->makeMapField(),
                    'percentage' => $this->makeMapField('discount'),
                    'applies_to' => [],
                ]
            ],
            'payment_schedules' => [],
        ];

        $this->putJson(route('invoices.imports.map', $import), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $import->refresh();
        $this->assertEquals($data, $import->mapping);
        $this->assertTrue($import->mapping_valid);
    }

    public function test_can_queue_import()
    {
        $this->assignPermission('create', InvoiceImport::class);
        Storage::fake();
        Queue::fake();

        $originalPath = InvoiceImport::storeFile(
            $this->getUploadedFile('sonar-import.xls'),
            $this->school
        );
        $import = InvoiceImport::create([
            'user_id' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => $originalPath,
            'heading_row' => 1,
            'starting_row' => 2,
            'mapping' => [
                'student_attribute' => 'student_number',
                'student_column' => 'student number',
                'title' => $this->makeMapField(value: 'Invoice title', isManual: true),
                'description' => $this->makeMapField(),
                'due_at' => $this->makeMapField('due date'),
                'available_at' => $this->makeMapField('available date'),
                'term_id' => $this->makeMapField(),
                'notify' => false,
                'items' => [
                    [
                        'fee_id' => $this->makeMapField(),
                        'name' => $this->makeMapField('invoice name'),
                        'amount_per_unit' => $this->makeMapField('invoice amount'),
                        'quantity' => $this->makeMapField(value: 1, isManual: true),
                    ],
                ],
                'scholarships' => [
                    [
                        'name' => $this->makeMapField(value: 'Assistance', isManual: true),
                        'use_amount' => false,
                        'amount' => $this->makeMapField(),
                        'percentage' => $this->makeMapField('discount'),
                        'applies_to' => [],
                    ]
                ],
                'payment_schedules' => [],
            ]
        ]);

        // Check that the import validity is checked
        $this->post(route('invoices.imports.start', $import))
            ->assertSessionHas('error')
            ->assertRedirect();

        // Check for valid mapping
        $import->mapping_valid = $import->hasValidMapping();
        $this->assertTrue($import->mapping_valid);
        $import->save();

        // Check that the import validity is checked
        $this->post(route('invoices.imports.start', $import))
            ->assertSessionHas('success')
            ->assertRedirect();

        Queue::assertPushed(ProcessInvoiceImport::class);
    }

    public function test_can_import_simple_xls()
    {
        $this->withoutExceptionHandling();
        Storage::fake();
        Event::fake();

        $originalPath = InvoiceImport::storeFile(
            $this->getUploadedFile('sonar-import.xls'),
            $this->school
        );
        $import = InvoiceImport::make([
            'user_id' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => $originalPath,
            'heading_row' => 1,
            'starting_row' => 2,
            'mapping' => [
                'student_attribute' => 'student_number',
                'student_column' => 'student number',
                'title' => $this->makeMapField(value: 'Invoice title', isManual: true),
                'description' => $this->makeMapField(),
                'due_at' => $this->makeMapField('due date'),
                'available_at' => $this->makeMapField('available date'),
                'term_id' => $this->makeMapField(),
                'notify' => false,
                'items' => [
                    [
                        'id' => $this->uuid(),
                        'fee_id' => $this->makeMapField(),
                        'name' => $this->makeMapField('invoice name'),
                        'amount_per_unit' => $this->makeMapField('invoice amount'),
                        'quantity' => $this->makeMapField(value: 1, isManual: true),
                    ],
                ],
                'scholarships' => [
                    [
                        'id' => $this->uuid(),
                        'name' => $this->makeMapField(value: 'Assistance', isManual: true),
                        'use_amount' => false,
                        'amount' => $this->makeMapField(),
                        'percentage' => $this->makeMapField('discount'),
                        'applies_to' => [],
                    ]
                ],
                'payment_schedules' => [],
            ]
        ]);
        $import->mapping_valid = $import->hasValidMapping();
        $import->setTotalRecords();
        $import->save();

        // Change the students to match the student numbers
        $students = $this->school->students->random($import->total_records);

        $import->getImportContents()
            ->each(function (Collection $row, $index) use ($students) {
                $studentNumber = $row->get('student number');

                if (!blank($studentNumber)) {
                    $students->get($index)->update(['student_number' => $row->get('student number')]);
                }
            });

        $job = new ProcessInvoiceImport($import);
        $job->handle();

        $import->refresh();
        $contents = $import->getImportContents();
        $contentsByStudentNumber = $contents->keyBy('student number');

        $values = $contents
            ->pluck('student number')
            ->filter(fn ($value) => !is_null($value));

        $students = $import->school->students()
            ->whereIn('student_number', $values)
            ->get();

        $students->each(function (Student $student) use ($contentsByStudentNumber) {
            $this->assertEquals(1, $student->invoices->count());
            /** @var Invoice $invoice */
            $invoice = $student->invoices->first();
            $this->assertEquals(1, $invoice->invoiceItems->count());
            $row = $contentsByStudentNumber->get($student->student_number);
            $total = $row['invoice amount'] * 100;
            $discount = 0;

            if (!empty($row['discount'])) {
                $this->assertEquals(1, $invoice->invoiceScholarships->count());
                /** @var InvoiceScholarship $scholarship */
                $scholarship = $invoice->invoiceScholarships->first();
                $discount = $scholarship->calculated_amount;
                $this->assertEquals(0, $scholarship->amount);
                $this->assertEquals($row['discount'], $scholarship->percentage);
                $this->assertEquals($total * $row['discount'], $scholarship->calculated_amount);
            }

            /** @var InvoiceItem $item */
            $item = $invoice->invoiceItems->first();
            $this->assertEquals($total, $item->amount);
            $this->assertEquals($total, $item->amount_per_unit);

            $due = $total - $discount;
            if ($due < 0) {
                $due = 0;
            }
            $this->assertEquals($due, $invoice->amount_due);
            $this->assertEquals($due, $invoice->remaining_balance);
        });

        $this->assertEquals(3, $import->imported_records);
        $this->assertEquals(1, $import->failed_records);
        $this->assertCount(4, $import->results);
        Event::assertDispatched(InvoiceImportFinished::class);
    }
}
