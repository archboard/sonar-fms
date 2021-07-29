<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\Assert;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    use RefreshDatabase;

    protected function setNewAppUrl()
    {
        $this->tenant->forget();
        $parsed = parse_url(url('/'));
        $url = "{$parsed['scheme']}://subdomaindoesnotexist.{$parsed['host']}";
        URL::forceRootUrl($url);
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
}
