<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChangeLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_change_locale()
    {
        $this->signIn();

        $this->post(route('locale'), ['locale' => 'zh'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals('zh', $this->user->locale);
        $this->assertEquals('zh', $this->app->getLocale());
    }
}
