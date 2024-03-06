<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSchoolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->user->update(['manages_tenancy' => true]);
    }

    public function test_school_access_list_function()
    {
        $user = $this->createUser();
        $schools = $user->getSchoolAccessList();

        $this->assertCount($this->tenant->schools->count(), $schools);

        $schools->each(function ($school) {
            $this->assertArrayHasKey('id', $school);
            $this->assertArrayHasKey('name', $school);
            $this->assertArrayHasKey('active', $school);
            $this->assertArrayHasKey('has_access', $school);
            $this->assertEquals($school['id'] === $this->school->id, $school['has_access']);
        });
    }

    public function test_gate()
    {
        $this->signIn(true);
        $user = $this->createUser();

        $this->get(route('users.schools', $user))
            ->assertForbidden();
    }

    public function test_can_retrieve_user_school_list()
    {
        $user = $this->createUser();

        $this->get(route('users.schools', $user))
            ->assertOk()
            ->assertJson($user->getSchoolAccessList()->toArray());
    }

    public function test_can_update_user_school_access_list()
    {
        $user = $this->createUser();
        $schools = $user->getSchoolAccessList()
            ->map(function ($school) {
                $school['has_access'] = true;

                return $school;
            })->toArray();

        $this->put(route('users.schools', $user), compact('schools'))
            ->assertOk()
            ->assertJsonStructure(['level', 'message']);

        $this->assertEquals(count($schools), $user->schools()->count());
    }

    public function test_cant_update_user_school_access_list_with_no_schools()
    {
        $user = $this->createUser();
        $schools = $user->getSchoolAccessList()
            ->map(function ($school) {
                $school['has_access'] = false;

                return $school;
            })->toArray();

        $this->put(route('users.schools', $user), compact('schools'))
            ->assertStatus(422)
            ->assertJsonStructure(['level', 'message']);

        $this->assertEquals(1, $user->schools()->count());
    }

    public function test_cant_update_user_school_access_list_with_no_active_schools()
    {
        $user = $this->createUser();
        $this->tenant->schools()
            ->update(['active' => false]);
        $schools = $user->getSchoolAccessList()
            ->map(function ($school) {
                $school['has_access'] = true;

                return $school;
            })->toArray();

        $this->put(route('users.schools', $user), compact('schools'))
            ->assertStatus(422)
            ->assertJsonStructure(['level', 'message']);

        $this->assertEquals(1, $user->schools()->count());
    }
}
