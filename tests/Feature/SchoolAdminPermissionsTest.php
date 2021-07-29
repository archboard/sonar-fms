<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class SchoolAdminPermissionsTest extends TestCase
{
    use RefreshDatabase;
    use SignsIn;

    public function test_can_not_toggle_school_admin_role_without_permission()
    {
        $user = $this->createUser();

        $this->put(route('users.school-admin', $user))
            ->assertForbidden();
    }

    public function test_toggles_users_admin_role()
    {
        $this->manageTenancy();
        $user = $this->createUser();

        $this->assertFalse($user->isSchoolAdmin());

        $this->put(route('users.school-admin', $user))
            ->assertJsonStructure(['level', 'message'])
            ->assertOk();

        $user->refresh();
        $this->assertTrue($user->isSchoolAdmin());

        $this->put(route('users.school-admin', $user))
            ->assertJsonStructure(['level', 'message'])
            ->assertOk();

        $user->refresh();
        $this->assertFalse($user->isSchoolAdmin());
    }

    public function test_toggles_users_admin_role_as_school_admin()
    {
        $this->user->assign('school admin');
        \Bouncer::refreshFor($this->user);
        $user = $this->createUser();

        $this->assertFalse($user->isSchoolAdmin());

        $this->put(route('users.school-admin', $user))
            ->assertJsonStructure(['level', 'message'])
            ->assertOk();

        $user->refresh();
        $this->assertTrue($user->isSchoolAdmin());
    }
}
