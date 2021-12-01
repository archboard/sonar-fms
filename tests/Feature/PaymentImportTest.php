<?php

namespace Tests\Feature;

use App\Models\PaymentImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\Assert;
use Tests\TestCase;
use Tests\Traits\GetsUploadedFiles;
use Tests\Traits\MapsFields;

class PaymentImportTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use MapsFields;
    use GetsUploadedFiles;

    protected bool $signIn = true;

    protected function createImport(string $file = 'small_payments.xlsx'): PaymentImport
    {
        $originalPath = (new PaymentImport)
            ->storeFile($this->getUploadedFile($file), $this->school);

        return PaymentImport::create([
            'tenant_id' => $this->tenant->id,
            'user_uuid' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => $originalPath,
        ]);
    }

    public function test_cant_view_imports_page_without_permission()
    {
        $this->get(route('payments.imports.index'))
            ->assertForbidden();
    }

    public function test_can_view_imports_with_permission()
    {
        $this->assignPermission('viewAny', PaymentImport::class);

        $this->get('/payments/imports')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('imports')
                ->has('permissions')
                ->component('payments/imports/Index')
            );
    }

    public function test_can_view_the_create_form()
    {
        $this->assignPermission('create', PaymentImport::class);

        $this->get(route('payments.imports.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('extensions')
                ->where('method', 'post')
                ->where('endpoint', route('payments.imports.store'))
                ->component('invoices/imports/Create')
            );
    }

    public function test_can_create_payment_import()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', PaymentImport::class);
        Storage::fake();

        $data = [
            'files' => [[
                'file' => $this->getUploadedFile('small_payments.xlsx'),
            ]],
            'heading_row' => 1,
            'starting_row' => 2,
        ];

        $this->post(route('payments.imports.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(1, $this->school->paymentImports()->count());
        $this->assertEquals(1, $this->user->paymentImports()->count());

        /** @var PaymentImport $import */
        $import = $this->user->paymentImports()->first();
        Storage::assertExists($import->file_path);
        $this->assertEquals(1, $import->heading_row);
        $this->assertEquals(2, $import->starting_row);
        $this->assertEquals(4, $import->total_records);
        $this->assertEquals(0, $import->imported_records);
        $this->assertEquals(0, $import->failed_records);
    }

    public function test_can_view_edit_import_page()
    {
        $this->assignPermission('update', PaymentImport::class);
        $import = PaymentImport::create([
            'tenant_id' => $this->tenant->id,
            'user_uuid' => $this->user->id,
            'school_id' => $this->school->id,
            'file_path' => '/tmp/file.csv',
        ]);

        $this->get(route('payments.imports.edit', $import))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('extensions')
                ->has('existingImport')
                ->where('method', 'put')
                ->where('endpoint', route('payments.imports.update', $import))
                ->component('invoices/imports/Create')
            );
    }

    public function test_can_view_update_existing_import_without_new_file()
    {
        $this->assignPermission('update', PaymentImport::class);
        Storage::fake();

        $import = $this->createImport('single_payment.csv');
        $data = [
            'files' => [[
                'file' => null,
                'existing' => true,
            ]],
            'heading_row' => 2,
            'starting_row' => 3,
        ];

        $this->put(route('payments.imports.update', $import), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $import->refresh();
        $this->assertEquals('single_payment.csv', $import->file_name);
        $this->assertEquals(2, $import->heading_row);
        $this->assertEquals(3, $import->starting_row);
        Storage::assertExists($import->file_path);
    }

    public function test_can_view_update_existing_import_with_new_file()
    {
        $this->assignPermission('update', PaymentImport::class);
        Storage::fake();

        $import = $this->createImport('single_payment.csv');
        $originalPath = $import->file_path;
        $data = [
            'files' => [[
                'file' => $this->getUploadedFile('small_payments.xlsx'),
            ]],
            'heading_row' => 1,
            'starting_row' => 2,
        ];

        $this->put(route('payments.imports.update', $import), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $import->refresh();
        $this->assertEquals('small_payments.xlsx', $import->file_name);
        $this->assertEquals(1, $import->heading_row);
        $this->assertEquals(2, $import->starting_row);
        $this->assertEquals(4, $import->total_records);
        Storage::assertExists($import->file_path);
        Storage::assertMissing($originalPath);
        Storage::assertMissing(dirname($originalPath));
    }

    public function test_can_get_to_mapping_page()
    {
        $this->assignPermission('update', PaymentImport::class);
        $import = $this->createImport();

        $this->get(route('payments.imports.map', $import))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('paymentImport')
                ->has('headers')
                ->component('payments/imports/Map')
            );
    }

    public function test_can_save_valid_mapping()
    {
        $this->assignPermission('update', PaymentImport::class);
        $import = $this->createImport();

        // Just do the bare minimum
        $data = [
            'invoice_column' => 'invoice number',
            'invoice_payment_term' => $this->makeMapField(),
            'payment_method' => $this->makeMapField(),
            'transaction_details' => $this->makeMapField(),
            'paid_at' => $this->makeMapField('date'),
            'amount' => $this->makeMapField('amount'),
            'made_by' => $this->makeMapField(),
            'notes' => $this->makeMapField(),
        ];

        $this->put(route('payments.imports.map', $import), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('payments.imports.show', $import));

        $import->refresh();
        $this->assertNotEmpty($import->mapping);
        $this->assertTrue($import->mapping_valid);
    }

    public function test_can_save_invalid_mapping()
    {
        $this->assignPermission('update', PaymentImport::class);
        $import = $this->createImport();

        // Just do the bare minimum
        $data = [
            'invoice_column' => null,
            'invoice_payment_term' => $this->makeMapField(),
            'payment_method' => $this->makeMapField(),
            'transaction_details' => $this->makeMapField(),
            'paid_at' => $this->makeMapField(),
            'amount' => $this->makeMapField(),
            'made_by' => $this->makeMapField(),
            'notes' => $this->makeMapField(),
        ];

        $this->put(route('payments.imports.map', $import), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('payments.imports.show', $import));

        $import->refresh();
        $this->assertNotEmpty($import->mapping);
        $this->assertFalse($import->mapping_valid);

        $errors = $import->getMappingValidationErrors();
        $this->assertCount(3, $errors);
        $this->assertArrayHasKey('invoice_column', $errors);
        $this->assertArrayHasKey('paid_at', $errors);
        $this->assertArrayHasKey('amount', $errors);
    }

    public function test_can_view_import_show_page()
    {
        $this->assignPermission('viewAny', PaymentImport::class);
        $import = $this->createImport();

        $this->get(route('payments.imports.show', $import))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('paymentImport')
                ->has('permissions')
                ->has('results')
                ->component('payments/imports/Show')
            );
    }
}
