<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;
    use SignsIn;

    public function test_cannot_change_permissions()
    {
        $user = $this->addUser();
        $this->put(route('users.permissions', $user), [])
            ->assertForbidden();
    }

    public function test_can_update_permissions()
    {
        $this->assignPermission('edit permissions', User::class);
        $user = $this->addUser();
        $permissions = [
            'models' => [
                [
                    'model' => User::class,
                    'permissions' => [
                        [
                            'permission' => 'view',
                            'can' => true,
                        ],
                        [
                            'permission' => 'create',
                            'can' => true,
                        ],
                        [
                            'permission' => 'update',
                            'can' => true,
                        ],
                        [
                            'permission' => 'delete',
                            'can' => true,
                        ],
                        [
                            'permission' => 'edit permissions',
                            'can' => false,
                        ],
                    ],
                ],
            ],
        ];

        $this->assertFalse($user->can('view', User::class));
        $this->assertFalse($user->can('create', User::class));
        $this->assertFalse($user->can('update', User::class));
        $this->assertFalse($user->can('delete', User::class));
        $this->assertFalse($user->can('edit permissions', User::class));

        $this->put(route('users.permissions', $user), $permissions)
            ->assertOk()
            ->assertJsonStructure([
                'level', 'message'
            ]);

        $this->assertTrue($user->can('view', User::class));
        $this->assertTrue($user->can('create', User::class));
        $this->assertTrue($user->can('update', User::class));
        $this->assertTrue($user->can('delete', User::class));
        $this->assertFalse($user->can('edit permissions', User::class));
    }
}
