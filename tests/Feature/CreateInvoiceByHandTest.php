<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class CreateInvoiceByHandTest extends TestCase
{
    use RefreshDatabase;
    use SignsIn;

    public function test_cannot_create_without_permission()
    {
        $this->get(route('invoices.create'))
            ->assertForbidden();
    }

    public function test_the_create_page_is_inertia_set()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('create', Invoice::class);

        $this->get(route('invoices.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('students')
                ->has('endpoint')
                ->has('method')
                ->component('invoices/Create')
            );
    }
}
