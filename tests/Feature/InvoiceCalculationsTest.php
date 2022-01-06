<?php

namespace Tests\Feature;

use App\Jobs\CalculateInvoiceAttributes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\CreatesInvoice;

class InvoiceCalculationsTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInvoice;

    protected bool $signIn = true;

    public function test_can_get_correct_numbers_for_parent_invoice_with_normal_children()
    {
        $parent = $this->createCombinedInvoice();
        $children = $parent->countableChildren();

        $parent->unsetRelations();
        $parent->setCalculatedAttributes(true);
        $parent->refresh();

        $this->assertEquals($children->sum('pre_tax_subtotal'), $parent->pre_tax_subtotal);
        $this->assertEquals($children->sum('discount_total'), $parent->discount_total);
        $this->assertEquals($children->sum('tax_due'), $parent->tax_due);
        $this->assertEquals($children->sum('amount_due'), $parent->amount_due);
        $this->assertEquals($children->sum('remaining_balance'), $parent->remaining_balance);
    }

    /**
     * This also tests the model updated event
     * of an invoice with a parent and either
     * a dirty void or published timestamp
     *
     * @return void
     */
    public function test_can_get_correct_numbers_for_parent_invoice_with_a_void_child()
    {
        $parent = $this->createCombinedInvoice();
        $void = $parent->children->random();
        $void->update(['voided_at' => now()]);

        $children = $parent->children()
            ->isNotVoid()
            ->published()
            ->get();

        $parent->refresh();

        $this->assertEquals($children->sum('pre_tax_subtotal'), $parent->pre_tax_subtotal);
        $this->assertEquals($children->sum('discount_total'), $parent->discount_total);
        $this->assertEquals($children->sum('tax_due'), $parent->tax_due);
        $this->assertEquals($children->sum('amount_due'), $parent->amount_due);
        $this->assertEquals($children->sum('remaining_balance'), $parent->remaining_balance);
    }

    public function test_can_get_correct_numbers_for_parent_invoice_with_a_void_and_unpublished_child()
    {
        Queue::fake();
        $parent = $this->createCombinedInvoice();
        [$void, $unpublished] = $parent->children->random(2);
        $parent->unsetRelations();
        $void->update(['voided_at' => now()]);
        $unpublished->update(['published_at' => null]);

        Queue::assertPushed(CalculateInvoiceAttributes::class);
        $children = $parent->children()
            ->isNotVoid()
            ->published()
            ->get();

        $this->assertEquals(1, $children->count());
        $this->assertEquals(1, $parent->countableChildren()->count());
        $parent->setCalculatedAttributes(true)->refresh();

        $this->assertEquals($children->sum('pre_tax_subtotal'), $parent->pre_tax_subtotal);
        $this->assertEquals($children->sum('discount_total'), $parent->discount_total);
        $this->assertEquals($children->sum('tax_due'), $parent->tax_due);
        $this->assertEquals($children->sum('amount_due'), $parent->amount_due);
        $this->assertEquals($children->sum('remaining_balance'), $parent->remaining_balance);
    }
}
