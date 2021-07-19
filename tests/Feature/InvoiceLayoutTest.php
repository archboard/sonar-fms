<?php

namespace Tests\Feature;

use App\Models\InvoiceLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->assertDatabaseHas('invoice_layouts', Arr::except($data, 'layout_data'));

        $layout = InvoiceLayout::first();
        $this->assertEquals($data['layout_data'], $layout->layout_data);
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
}
