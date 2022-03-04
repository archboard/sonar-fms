<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Fee;
use App\Models\FeeCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\ResponseTrait;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class FeeTest extends TestCase
{
    use RefreshDatabase;
    use SignsIn;

    public function test_permissions_work_for_index_page()
    {
        $this->get(route('fees.index'))
            ->assertForbidden();
    }

    public function test_index_is_ok()
    {
        $this->assignPermission('view', Fee::class);

        $this->get(route('fees.index'))
            ->assertOk();
    }

    public function test_can_create_new_simple_fee()
    {
        $this->assignPermission('create', Fee::class);

        $data = [
            'name' => 'Tuition',
            'code' => 'TUIT',
            'description' => 'This is a fee for tuition.',
            'amount' => 25000000,
        ];

        $this->post(route('fees.store'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $this->assertDatabaseHas('fees', $data);
    }

    public function test_can_create_new_fee_with_all_the_fixins()
    {
        $this->assignPermission('create', Fee::class);
        $category = FeeCategory::factory()->create();
        $department = Department::factory()->create();

        $data = [
            'name' => 'Tuition',
            'code' => 'TUIT',
            'description' => 'This is a fee for tuition.',
            'amount' => 25000000,
            'fee_category_id' => $category->id,
            'department_id' => $department->id,
        ];

        $this->post(route('fees.store'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $this->assertDatabaseHas('fees', $data);
    }

    public function test_can_update_fee()
    {
        $this->assignPermission('update', Fee::class);
        $fee = $this->school->fees()->save(
            Fee::factory()->make(['tenant_id' => $this->tenant->id])
        );
        $category = FeeCategory::factory()->create();
        $department = Department::factory()->create();

        $data = [
            'name' => 'Tuition',
            'code' => 'TUIT',
            'description' => 'This is a fee for tuition.',
            'amount' => 25000000,
            'fee_category_id' => $category->id,
            'department_id' => $department->id,
        ];

        $this->put(route('fees.update', $fee), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $this->assertDatabaseHas('fees', $data);
    }

    public function test_can_delete_fee()
    {
        $this->assignPermission('delete', Fee::class);
        $fee = $this->school->fees()->save(
            Fee::factory()->make(['tenant_id' => $this->tenant->id])
        );

        $this->delete(route('fees.destroy', $fee))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('fees', ['id' => $fee->id]);
    }
}
