<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_get_all_departments_without_permission()
    {
        $this->get(route('departments.index'))
            ->assertForbidden();
    }

    public function test_can_get_all_departments_with_permission()
    {
        $this->signIn();
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
}
