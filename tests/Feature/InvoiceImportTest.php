<?php

namespace Tests\Feature;

use App\Models\InvoiceImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
                'file' => UploadedFile::fake()->create('import.xls', 2, 'application/vnd.ms-excel')
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

        $originalPath = InvoiceImport::storeFile(UploadedFile::fake()->create('original.xlsx', 2), $this->school);
        $import = InvoiceImport::create([
            'user_id' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => $originalPath,
        ]);

        $data = [
            'files' => [[
                'file' => UploadedFile::fake()->create('new-file.csv', 2)
            ]],
            'heading_row' => 2,
            'starting_row' => 3,
        ];

        $this->put(route('invoices.imports.update', $import), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $import->refresh();
        $this->assertEquals('new-file.csv', $import->file_name);
        $this->assertEquals(2, $import->heading_row);
        $this->assertEquals(3, $import->starting_row);
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
            new UploadedFile(base_path('tests/sonar-import.xlsx'), 'sonar-import.xlsx', null, null, true),
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
            'title' => $this->makeMapField(null, 'Invoice title', true),
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
                    'quantity' => $this->makeMapField(null, 1, true),
                ],
            ],
            'scholarships' => [
                [
                    'name' => $this->makeMapField(null, 'Assistance', true),
                    'use_amount' => false,
                    'amount' => $this->makeMapField(),
                    'percentage' => $this->makeMapField('discount'),
                    'applies_to' => [],
                ]
            ],
            'payment_schedules' => [],
        ];

        $response = $this->putJson(route('invoices.imports.map', $import), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $import->refresh();
        $this->assertEquals($data, $import->mapping);
    }
}
