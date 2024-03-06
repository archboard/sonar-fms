<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class MyInvoicesTest extends TestCase
{
    use CreatesInvoice;
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
                ->has('invoices')
                ->component('my-invoices/Index')
            );
    }

    public function test_can_view_invoice_page()
    {
        $student = $this->user->students->random();
        $invoice = $this->createInvoice(['student_uuid' => $student->uuid]);

        $this->get(route('my-invoices.show', $invoice))
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('title')
                ->has('invoice')
                ->has('permissions')
                ->component('my-invoices/Show')
            );
    }
}
