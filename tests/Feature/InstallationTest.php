<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setNewAppUrl(): string
    {
        $this->tenant->forget();
        $parsed = parse_url(url('/'));
        $url = "{$parsed['scheme']}://subdomaindoesnotexist.{$parsed['host']}";
        URL::forceRootUrl($url);

        return $url;
    }

    public function test_cant_see_installation_page_if_cloud()
    {
        config()->set('app.cloud', true);

        $this->get(route('install'))
            ->assertNotFound();
    }

    public function test_cannot_install_if_logged_in_and_no_permission()
    {
        $this->signIn();

        $this->get(route('install'))
            ->assertForbidden();
    }

    public function test_cant_see_installation_page_if_tenancy_is_installed()
    {
        $this->signIn();
        \Bouncer::allow($this->user)->to('install tenant');

        $this->get(route('install'))
            ->assertNotFound();
    }

    public function test_can_see_installation_page()
    {
        $this->setNewAppUrl();

        $this->get(route('install'))
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (Assert $page) => $page
                ->has('title')
                ->has('tenant')
                ->has('email')
            );
    }

    public function test_can_install_tenant()
    {
        $this->withoutExceptionHandling();
        $url = $this->setNewAppUrl();
        $parsed = parse_url($url);

        // Ideally this would be done with a mock, but I don't know how it would work
        //        $jobMock = $this->getMockBuilder(SyncSchools::class)
        //            ->onlyMethods(['handle'])
        //            ->getMock();
        //
        //        $jobMock->expects($this->once())
        //            ->method('handle')
        //            ->will($this->returnSelf());

        $data = [
            'license' => $this->uuid(),
            'name' => $this->faker->company,
            'domain' => $parsed['host'],
            'ps_url' => $this->faker->url,
            'ps_client_id' => $this->uuid(),
            'ps_secret' => $this->uuid(),
            'email' => $this->faker->email,
        ];

        $this->post(route('install'), $data)
            ->assertSessionHas('success')
            ->assertRedirect(route('settings.tenant'));

        /** @var User $user */
        $user = User::firstWhere('email', $data['email']);
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->manages_tenancy);
        $this->assertDatabaseHas('tenants', Arr::except($data, 'email'));

        $tenant = Tenant::firstWhere('domain', $data['domain']);
        $this->assertEquals($tenant->schools->count(), $user->schools->count());
    }
}
