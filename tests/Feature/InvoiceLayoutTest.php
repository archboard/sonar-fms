<?php

namespace Tests\Feature;

use App\Models\InvoiceLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Inertia\Testing\Assert;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class InvoiceLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected bool $signIn = true;

    protected function setUp(): void
    {
        parent::setUp();
        InvoiceLayout::whereNotNull('id')->delete();
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
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('permissions')
                ->has('layouts')
                ->component('layouts/Index')
            );
    }

    public function test_can_get_to_create_layout_page()
    {
        $this->assignPermission('create', InvoiceLayout::class);

        $this->get(route('layouts.create'))
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->where('method', 'post')
                ->where('endpoint', route('layouts.store'))
            );
    }

    public function test_can_create_a_new_layout()
    {
        $this->assignPermission('create', InvoiceLayout::class);

        $data = [
            'name' => 'My invoice layout',
            'locale' => null,
            'paper_size' => 'A4',
            'layout_data' => [
                'rows' => [
                    [
                        'isInvoiceTable' => false,
                        'columns' => [
                            [
                                'content' => '<p>My layout content</p>',
                            ]
                        ],
                    ],
                    [
                        'isInvoiceTable' => true,
                        'columns' => [],
                    ],
                ],
                'primary' => '#fff',
            ],
        ];

        $this->post(route('layouts.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('layouts.index'));

        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $data['is_default'] = true;
        $this->assertDatabaseHas('invoice_layouts', Arr::except($data, 'layout_data'));

        $layout = InvoiceLayout::first();
        $this->assertEquals($data['layout_data'], $layout->layout_data);
    }

    public function test_can_save_and_preview_a_new_layout()
    {
        $this->assignPermission('create', InvoiceLayout::class);

        $data = [
            'name' => 'My invoice layout',
            'locale' => null,
            'paper_size' => 'A4',
            'layout_data' => [
                'rows' => [
                    [
                        'isInvoiceTable' => false,
                        'columns' => [
                            [
                                'content' => '<p>My layout content</p>',
                            ]
                        ],
                    ],
                    [
                        'isInvoiceTable' => true,
                        'columns' => [],
                    ],
                ],
                'primary' => '#fff',
            ],
            'preview' => true,
        ];

        $this->post(route('layouts.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('layouts.edit', InvoiceLayout::first()));
    }

    public function test_can_get_to_edit_page()
    {
        $this->assignPermission('update', InvoiceLayout::class);

        $layout = InvoiceLayout::factory()->create();

        $this->get(route('layouts.edit', $layout))
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('layout')
                ->where('endpoint', route('layouts.update', $layout))
                ->where('method', 'put')
                ->where('preview', route('layouts.preview', $layout))
            );
    }

    public function test_can_update_existing_layout()
    {
        $this->assignPermission('update', InvoiceLayout::class);

        /** @var InvoiceLayout $layout */
        $layout = InvoiceLayout::factory()->create();
        $data = [
            'name' => 'My invoice layout',
            'locale' => 'en',
            'paper_size' => 'Letter',
            'layout_data' => [
                'rows' => [
                    [
                        'isInvoiceTable' => false,
                        'columns' => [
                            [
                                'content' => '<p>My layout content</p>',
                            ]
                        ],
                    ],
                    [
                        'isInvoiceTable' => true,
                        'columns' => [],
                    ],
                ],
                'primary' => '#fff',
            ],
        ];

        $this->put(route('layouts.update', $layout), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $layout->refresh();
        $this->assertEquals($data['layout_data'], $layout->layout_data);
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

    public function test_layout_needs_invoice_table_row()
    {
        $this->assignPermission('create', InvoiceLayout::class);

        $data = [
            'name' => 'My invoice layout',
            'locale' => 'en',
            'paper_size' => 'Letter',
            'layout_data' => [
                'rows' => [
                    [
                        'isInvoiceTable' => false,
                        'columns' => [
                            [
                                'content' => '<p>My layout content</p>',
                            ]
                        ],
                    ],
                ],
                'primary' => '#fff',
            ],
        ];

        $this->postJson(route('layouts.store'), $data)
            ->assertJsonValidationErrors(['layout_data'])
            ->assertStatus(422);
    }

    public function test_can_switch_default_layout()
    {
        $this->assignPermission('update', InvoiceLayout::class);

        $default = InvoiceLayout::factory()->create(['is_default' => true]);
        $this->assertTrue($default->is_default);
        $other = InvoiceLayout::factory()->create(['is_default' => false]);
        $this->assertFalse($other->is_default);

        $this->post(route('layouts.default', $other))
            ->assertSessionHas('success')
            ->assertRedirect();

        $default->refresh();
        $this->assertFalse($default->is_default);
        $other->refresh();
        $this->assertTrue($other->is_default);
    }

    public function test_can_retrieve_default_layout_for_school()
    {
        $this->assignPermission('update', InvoiceLayout::class);

        $others = InvoiceLayout::factory()->count(3)->create(['is_default' => false]);
        $default = InvoiceLayout::factory()->create(['is_default' => true]);

        $this->assertEquals($default->id, $this->school->getDefaultInvoiceLayout()->id);
    }
}
