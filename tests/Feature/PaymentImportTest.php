<?php

namespace Tests\Feature;

use App\Models\PaymentImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\Assert;
use Tests\TestCase;

class PaymentImportTest extends TestCase
{
    use RefreshDatabase;

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
}
