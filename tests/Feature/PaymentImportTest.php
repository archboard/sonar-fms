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
}
