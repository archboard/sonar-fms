<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MyInvoicesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $signIn = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpContact();
    }

    public function test_can_access_my_invoices()
    {
        $this->get('/my-invoices')
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('title')
                ->has('endpoint')
                ->where('canSelect', false)
                ->has('invoices')
                ->has('permissions')
                ->component('invoices/Index')
            );
    }
}
