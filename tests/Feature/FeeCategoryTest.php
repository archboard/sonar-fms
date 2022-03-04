<?php

namespace Tests\Feature;

use App\Http\Resources\FeeCategoryResource;
use App\Models\FeeCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class FeeCategoryTest extends TestCase
{
    use RefreshDatabase;
    use SignsIn;

    public function test_cannot_get_categories_without_permission()
    {
        $this->get(route('fee-categories.index'))
            ->assertForbidden();
    }

    public function test_can_get_all_categories()
    {
        $this->assignPermission('view', FeeCategory::class);

        $this->tenant->feeCategories()
            ->saveMany(FeeCategory::factory()->count(2)->make());

        $feeCategories = FeeCategory::orderBy('name')->get();

        $this->get(route('fee-categories.index'))
            ->assertOk()
            ->assertJson(FeeCategoryResource::collection($feeCategories)->response()->getData(true));
    }

    public function test_can_create_new_category()
    {
        $this->assignPermission('create', FeeCategory::class);

        $this->post(route('fee-categories.store'), ['name' => 'Fee Category Name'])
            ->assertOk()
            ->assertJsonStructure([
                'level', 'message', 'data'
            ]);

        $this->assertDatabaseHas('fee_categories', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Fee Category Name',
        ]);
    }

    public function test_can_get_single_fee_category()
    {
        $this->assignPermission('view', FeeCategory::class);

        /** @var FeeCategory $category */
        $category = $this->tenant->feeCategories()
            ->save(FeeCategory::factory()->make());

        $this->get(route('fee-categories.show', $category))
            ->assertOk()
            ->assertJson($category->toResource()->response()->getData(true));
    }

    public function test_can_update_a_fee_category()
    {
        $this->assignPermission('update', FeeCategory::class);

        /** @var FeeCategory $category */
        $category = $this->tenant->feeCategories()
            ->save(FeeCategory::factory()->make());

        $this->put(route('fee-categories.update', $category), ['name' => 'Updated Name'])
            ->assertOk()
            ->assertJsonStructure([
                'level', 'message', 'data',
            ]);

        $category->refresh();
        $this->assertEquals('Updated Name', $category->name);
    }

    public function test_can_delete_category()
    {
        /** @var FeeCategory $category */
        $category = $this->tenant->feeCategories()
            ->save(FeeCategory::factory()->make());

        $this->delete(route('fee-categories.destroy', $category))
            ->assertForbidden();

        $this->assignPermission('delete', FeeCategory::class);
        \Bouncer::refreshFor($this->user);

        $this->delete(route('fee-categories.destroy', $category))
            ->assertOk()
            ->assertJsonStructure([
                'level', 'message',
            ]);
    }
}
