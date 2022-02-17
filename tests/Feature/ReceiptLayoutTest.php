<?php

namespace Tests\Feature;

use App\Models\ReceiptLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_can_view_create_page()
    {
        $this->assignPermission('create', ReceiptLayout::class);

        $this->get(route('receipt-layouts.create'))
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('breadcrumbs')
                ->where('endpoint', route('receipt-layouts.store'))
                ->where('method', 'post')
                ->component('layouts/Create')
            );
    }

    public function test_can_create_a_new_layout()
    {
        $this->assignPermission('create', ReceiptLayout::class);

        $data = [
            'name' => 'My receipt layout',
            'locale' => null,
            'paper_size' => 'Letter',
            'layout_data' => [
                'rows' => [
                    [
                        'isContentTable' => false,
                        'columns' => [
                            [
                                'content' => '<p>My layout content</p>',
                            ]
                        ],
                    ],
                    [
                        'isContentTable' => true,
                        'columns' => [],
                    ],
                ],
            ],
        ];

        $this->post(route('receipt-layouts.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('receipt-layouts.index'));

        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $data['is_default'] = true;
        $this->assertDatabaseHas('receipt_layouts', Arr::except($data, 'layout_data'));

        $layout = ReceiptLayout::first();
        $this->assertEquals($data['layout_data'], $layout->layout_data);
    }

    public function test_can_save_and_preview_new_layout()
    {
        $this->assignPermission('create', ReceiptLayout::class);

        $data = [
            'name' => 'My layout',
            'locale' => null,
            'paper_size' => 'A4',
            'layout_data' => [
                'rows' => [
                    [
                        'isContentTable' => false,
                        'columns' => [
                            [
                                'content' => '<p>My layout content</p>',
                            ]
                        ],
                    ],
                    [
                        'isContentTable' => true,
                        'columns' => [],
                    ],
                ],
            ],
            'preview' => true,
        ];

        $this->post(route('receipt-layouts.store'), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('receipt-layouts.edit', ReceiptLayout::first()));
    }

    public function test_can_view_edit_page()
    {
        $this->assignPermission('update', ReceiptLayout::class);

        $layout = ReceiptLayout::factory()->create();

        $this->get(route('receipt-layouts.edit', $layout))
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('layout')
                ->where('breadcrumbs', fn ($prop) => count($prop) > 1)
                ->where('endpoint', route('receipt-layouts.update', $layout))
                ->where('method', 'put')
                ->where('preview', route('receipt-layouts.preview', $layout))
            );
    }

    public function test_can_update_layout()
    {
        $this->assignPermission('update', ReceiptLayout::class);

        /** @var ReceiptLayout $layout */
        $layout = ReceiptLayout::factory()->create();
        $data = [
            'name' => 'My layout',
            'locale' => 'en',
            'paper_size' => 'A4',
            'layout_data' => [
                'rows' => [
                    [
                        'isContentTable' => false,
                        'columns' => [
                            [
                                'content' => '<p>My layout content</p>',
                            ]
                        ],
                    ],
                    [
                        'isContentTable' => true,
                        'columns' => [],
                    ],
                ],
            ],
        ];

        $this->put(route('receipt-layouts.update', $layout), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $layout->refresh();
        $this->assertEquals($data['layout_data'], $layout->layout_data);
        $this->assertEquals($data['name'], $layout->name);
        $this->assertEquals($data['locale'], $layout->locale);
    }

    public function test_can_delete_a_layout()
    {
        $this->assignPermission('delete', ReceiptLayout::class);

        /** @var ReceiptLayout $layout */
        $layout = ReceiptLayout::factory()->create();

        $this->delete(route('receipt-layouts.destroy', $layout))
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertModelMissing($layout);
    }

    public function test_can_switch_layout_defaults()
    {
        $this->assignPermission('update', ReceiptLayout::class);

        $default = ReceiptLayout::factory()->create(['is_default' => true]);
        $this->assertTrue($default->is_default);
        $other = ReceiptLayout::factory()->create(['is_default' => false]);
        $this->assertFalse($other->is_default);

        $this->post(route('receipt-layouts.default', $other))
            ->assertSessionHas('success')
            ->assertRedirect();

        $default->refresh();
        $this->assertFalse($default->is_default);
        $other->refresh();
        $this->assertTrue($other->is_default);
    }
}
