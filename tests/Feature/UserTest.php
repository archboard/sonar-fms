<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class UserTest extends TestCase
{
    use RefreshDatabase;
    use SignsIn;

    public function test_can_get_to_users_index()
    {
        $this->withoutExceptionHandling();
        $this->assignPermission('view', User::class);

        $this->get(route('users.index'))
            ->assertOk();
    }

    public function test_can_create_a_new_non_existing_user()
    {
        $this->assignPermission('create', User::class);

        $data = [
            'first_name' => 'Michael',
            'last_name' => 'Jordan',
            'email' => 'mike@nba.com',
        ];

        $this->post(route('users.store'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $this->assertDatabaseHas('users', $data);

        $user = User::where('email', $data['email'])->first();
        $this->assertTrue($user->schools->contains('id', $this->school->id));
    }

    public function test_can_handle_adding_an_existing_user_to_same_school()
    {
        $this->assignPermission('create', User::class);

        $data = [
            'first_name' => 'Michael',
            'last_name' => 'Jordan',
            'email' => 'mike@nba.com',
        ];
        /** @var User $user */
        $user = $this->tenant->users()
            ->create($data);
        $user->schools()->sync([$this->school->id]);

        $this->post(route('users.store'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(1, $user->schools->count());
        $this->assertTrue($user->schools->contains('id', $this->school->id));
    }

    public function test_can_add_existing_user_to_current_school()
    {
        $this->assignPermission('create', User::class);

        $otherSchool = School::where('id', '!=', $this->school->id)->first();
        $data = [
            'first_name' => 'Michael',
            'last_name' => 'Jordan',
            'email' => 'mike@nba.com',
        ];
        /** @var User $user */
        $user = $this->tenant->users()
            ->create($data);
        $user->schools()->sync([$otherSchool->id]);

        $this->post(route('users.store'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(2, $user->schools->count());
        $this->assertTrue($user->schools->contains('id', $this->school->id));
        $this->assertTrue($user->schools->contains('id', $otherSchool->id));
    }
}
