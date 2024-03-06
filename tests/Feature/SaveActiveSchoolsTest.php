<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class SaveActiveSchoolsTest extends TestCase
{
    use RefreshDatabase;
    use SignsIn;

    public function test_cant_save_without_permission()
    {
        $this->put(route('tenant.schools'), [$this->school->id])
            ->assertForbidden();
    }

    public function test_cant_save_when_is_cloud()
    {
        $this->manageTenancy();
        Config::set('app.cloud', true);

        $this->put(route('tenant.schools'), ['schools' => [$this->school->id]])
            ->assertNotFound();
    }

    public function test_can_save_when_is_cloud()
    {
        $this->manageTenancy();

        $this->put(route('tenant.schools'), ['schools' => [$this->school->id]])
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(1, $this->tenant->schools()->active()->count());
        $this->assertEquals(2, $this->tenant->schools()->inactive()->count());
    }
}
