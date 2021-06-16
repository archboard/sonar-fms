<?php

namespace Tests\Feature;

use App\Models\InvoiceImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
