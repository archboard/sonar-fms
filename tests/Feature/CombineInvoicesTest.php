<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Inertia\Testing\Assert;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class CombineInvoicesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;

    protected bool $signIn = true;
    protected Collection $selection;

    protected function createSelection()
    {
        $invoice1 = $this->createInvoice();
        $invoice2 = $this->createInvoice();

        $this->selectInvoice($invoice1);
        $this->selectInvoice($invoice2);

        $this->selection = collect([$invoice1, $invoice2]);
    }

    public function test_need_permission_to_combine_invoices()
    {
        $this->get('/combine')
            ->assertForbidden();
    }

    public function test_cant_combine_without_a_selection()
    {
        $this->assignPermission('create', Invoice::class);

        $this->get('/combine')
            ->assertSessionHas('error')
            ->assertRedirect();
    }

    public function test_can_get_to_combine_page()
    {
        $this->assignPermission('create', Invoice::class);
        $this->createSelection();

        $this->get('/combine')
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('selection')
                ->component('invoices/Combine')
            );
    }

    public function test_can_join_invoices()
    {
        $this->createSelection();

        $this->assertEquals($this->selection->count(), $this->user->invoiceSelections()->count());
    }
}
