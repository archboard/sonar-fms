<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant->update(['allow_password_auth' => true]);
    }

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $this->withoutExceptionHandling();
        $user = $this->createUser();

        $this->post('/login', [
            'email' => strtoupper($user->email),
            'password' => 'password',
        ])
            ->assertRedirect(RouteServiceProvider::HOME);

        $this->assertAuthenticated();

        $activity = Activity::first();
        $this->assertNotNull($activity);
        $this->assertStringContainsString('successfully', Activity::first()->description);
        $this->assertEquals('auth', $activity->log_name);
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = $this->createUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertSessionHasErrors();

        $this->assertGuest();

        $activity = Activity::first();
        $this->assertNotNull($activity);
        $this->assertStringContainsString('failed', Activity::first()->description);
        $this->assertEquals('auth', $activity->log_name);
    }
}
