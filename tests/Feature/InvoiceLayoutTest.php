<?php

namespace Tests\Feature;

use App\Models\InvoiceLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class InvoiceLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_need_permission_to_get_to_layouts_page()
    {
        $this->get(route('layouts.index'))
            ->assertForbidden();
    }

    public function test_can_get_to_layouts_page()
    {
        $this->assignPermission('viewAny', InvoiceLayout::class);

        $this->get(route('layouts.index'))
            ->assertOk();
    }

    public function test_can_get_to_create_layout_page()
    {
        $this->assignPermission('create', InvoiceLayout::class);

        $this->get(route('layouts.create'))
            ->assertOk();
    }

    public function test_can_create_a_new_layout()
    {
        $this->assignPermission('create', InvoiceLayout::class);

        $data = [
            'name' => 'My invoice layout',
            'locale' => null,
            'data' => [
                'rows' => [],
                'primary' => '#fff',
                'logo' => '',
            ],
        ];

        $this->post(route('layouts.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('layouts.index'));

        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $this->assertDatabaseHas('invoice_layouts', Arr::except($data, 'data'));

        $layout = InvoiceLayout::first();
        $this->assertEquals($data['data'], $layout->data);
    }

    public function test_can_get_to_edit_page()
    {
        $this->assignPermission('update', InvoiceLayout::class);

        $layout = InvoiceLayout::factory()->create();

        $this->get(route('layouts.edit', $layout))
            ->assertViewHas('title')
            ->assertOk();
    }

    public function test_can_update_existing_layout()
    {
        $this->assignPermission('update', InvoiceLayout::class);

        /** @var InvoiceLayout $layout */
        $layout = InvoiceLayout::factory()->create();
        $data = [
            'name' => 'My invoice layout',
            'locale' => 'en',
            'data' => [
                'rows' => [],
                'primary' => '#00aabb',
                'logo' => 'my logo path that does not exist',
            ],
        ];

        $this->put(route('layouts.update', $layout), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $layout->refresh();
        $this->assertEquals($data['data'], $layout->data);
        $this->assertEquals($data['name'], $layout->name);
        $this->assertEquals($data['locale'], $layout->locale);
    }

    public function test_can_delete_layout()
    {
        $this->assignPermission('delete', InvoiceLayout::class);

        $layout = InvoiceLayout::factory()->create();

        $this->delete(route('layouts.destroy', $layout))
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertDatabaseMissing('invoice_layouts', ['id' => $layout->id]);
    }
}
