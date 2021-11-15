<?php

namespace Tests\Feature;

use App\Models\InvoicePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\Assert;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected bool $signIn = true;

    public function test_cant_list_all_payments_without_permissions()
    {
        $this->get('/payments')
            ->assertForbidden();
    }

    public function test_can_list_payments_with_permission()
    {
        $this->assignPermission('viewAny', InvoicePayment::class);

        $this->get('/payments')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('payments')
                ->component('payments/Index')
            );
    }
}
