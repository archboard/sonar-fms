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
            ]]
        ];

        $this->post(route('invoices.imports.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(1, $this->school->invoiceImports()->count());
        $this->assertEquals(1, $this->user->invoiceImports()->count());

        /** @var InvoiceImport $import */
        $import = $this->user->invoiceImports()->first();
        Storage::assertExists($import->file_path);
    }
}
