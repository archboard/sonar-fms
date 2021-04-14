<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_cannot_get_all_departments_without_permission()
    {
        $this->get(route('departments.index'))
            ->assertForbidden();
    }

    public function test_can_get_all_departments_with_permission()
    {
        $this->assignPermission('viewAny', Department::class);

        $this->tenant->departments()
            ->saveMany(Department::factory()->count(3)->make());

        $departments = $this->tenant->departments()
            ->orderBy('name')
            ->get();

        $this->get(route('departments.index'))
            ->assertOk()
            ->assertJson(Department::resource($departments)->response()->getData(true));
    }

    public function test_can_create_new_department()
    {
        $this->assignPermission('create', Department::class);

        $this->post(route('departments.store'), ['name' => 'Department Name'])
            ->assertOk()
            ->assertJsonStructure([
                'level', 'message', 'data',
            ]);

        $this->assertDatabaseHas('departments', [
            'tenant_id' => $this->tenant->id,
            'id' => 1,
            'name' => 'Department Name'
        ]);
    }

    public function test_can_get_existing_department()
    {
        $this->assignPermission('view', Department::class);

        /** @var Department $department */
        $department = $this->tenant->departments()
            ->save(Department::factory()->make());

        $this->get(route('departments.show', $department))
            ->assertOk()
            ->assertJson($department->toResource()->response()->getData(true));
    }

    public function test_cant_get_existing_department_without_permission()
    {
        /** @var Department $department */
        $department = $this->tenant->departments()
            ->save(Department::factory()->make());

        $this->get(route('departments.show', $department))
            ->assertForbidden();
    }
}
