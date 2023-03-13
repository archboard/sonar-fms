<?php

namespace Tests\Feature;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SchoolSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected bool $signIn = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->schools()->detach();
        $this->user->update(['school_id' => null]);
    }

    public function test_school_middleware_redirects()
    {
        $this->get('/home')
            ->assertRedirect('/select-school');
    }

    public function test_can_view_school_selection()
    {
        $schools = $this->tenant->schools()
            ->where('active', true)
            ->get();

        $this->get(route('missing-school'))
            ->assertViewHas('title')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('title')
                ->has('schools', $schools->count())
            );
    }

    public function test_can_update_school_from_school_selection()
    {
        $schools = $this->tenant->schools()
            ->where('active', true)
            ->get();
        $data = [
            'school_id' => $schools->random()->id,
        ];

        $this->put(route('missing-school'), $data)
            ->assertSessionHas('success')
            ->assertRedirect(RouteServiceProvider::HOME);

        $this->user->refresh();
        $this->assertEquals($data['school_id'], $this->user->school_id);
        $this->assertEquals(1, $this->user->schools()->count());
        $this->assertEquals($data['school_id'], $this->user->schools()->first()->id);
    }
}
