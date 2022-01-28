<?php

namespace Tests\Feature;

use App\Models\ReceiptLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\Assert;
use Tests\TestCase;

class ReceiptLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected bool $signIn = true;

    public function test_cant_access_without_permission()
    {
        $this->get(route('receipt-layouts.index'))
            ->assertForbidden();
    }

    public function test_can_access_listing_with_permission()
    {
        $this->assignPermission('viewAny', ReceiptLayout::class);

        $this->get(route('receipt-layouts.index'))
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('permissions')
                ->has('layouts')
                ->component('layouts/receipts/Index')
            );
    }
}
