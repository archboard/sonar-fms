<?php

namespace Tests\Feature;

use App\Models\InvoiceImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
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
        ];

        $this->put(route('invoices.imports.update', $import), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $import->refresh();
        $this->assertEquals('new-file.csv', $import->file_name);
        $this->assertEquals(2, $import->heading_row);
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
        ];

        $this->put(route('invoices.imports.update', $import), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $import->refresh();
        $this->assertEquals('original.xlsx', $import->file_name);
        $this->assertEquals(2, $import->heading_row);
        Storage::assertExists($import->file_path);
    }
}
