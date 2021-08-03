<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\PaymentMethods\Cash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\Assert;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_payment_method_permissions_are_working()
    {
        $this->get(route('payment-methods.index'))
            ->assertForbidden();
    }

    public function test_can_access_payment_method_index_page()
    {
        $this->assignPermission('viewAny', PaymentMethod::class);

        $this->get(route('payment-methods.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('paymentMethods')
                ->component('payment-methods/Index')
            );
    }

    public function test_can_access_create_page()
    {
        $this->assignPermission('create', PaymentMethod::class);

        $this->get(route('payment-methods.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('drivers')
                ->component('payment-methods/Create')
            );
    }

    public function test_can_create_new_cash_payment_method()
    {
        $this->assignPermission('create', PaymentMethod::class);

        $data = [
            'driver' => 'cash',
            'active' => false,
            'show_on_invoice' => false,
            'invoice_description' => 'My description',
            'options' => [
                'instructions' => 'These are my instructions',
            ],
        ];

        $this->post(route('payment-methods.store'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        /** @var PaymentMethod $method */
        $method = $this->school->paymentMethods()->first();
        $this->assertFalse($method->active);
        $this->assertFalse($method->show_on_invoice);
        $this->assertEquals('cash', $method->driver);
        $this->assertInstanceOf(Cash::class, $method->getDriver());
    }

    public function test_cannot_create_duplicate_payment_method_driver()
    {
        $this->assignPermission('create', PaymentMethod::class);

        PaymentMethod::factory()->create(['driver' => 'cash']);

        $data = [
            'driver' => 'cash',
            'active' => false,
            'show_on_invoice' => false,
            'invoice_description' => 'My description',
            'options' => [
                'instructions' => 'These are my instructions',
            ],
        ];

        $this->post(route('payment-methods.store'), $data)
            ->assertSessionHasErrors('driver');
    }

    public function test_can_show_edit_page()
    {
        $this->assignPermission('update', PaymentMethod::class);

        /** @var PaymentMethod $method */
        $method = PaymentMethod::factory()->create(['driver' => 'cash']);

        $this->get(route('payment-methods.edit', $method))
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->has('paymentMethod')
                ->has('drivers')
            );
    }

    public function test_update_existing_payment_method()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('update', PaymentMethod::class);

        /** @var PaymentMethod $method */
        $method = PaymentMethod::factory()->create(['driver' => 'cash']);

        $data = [
            'driver' => 'cash',
            'active' => false,
            'show_on_invoice' => false,
            'invoice_description' => 'My description',
            'options' => [
                'instructions' => 'These are my instructions',
            ],
        ];

        $this->put(route('payment-methods.update', $method), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('payment-methods.index'));

        $method->refresh();

        $this->assertFalse($method->active);
        $this->assertFalse($method->show_on_invoice);
        $this->assertEquals($data['invoice_description'], $method->invoice_description);
        $this->assertEquals($data['options'], $method->options);
    }
}
