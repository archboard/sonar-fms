<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SignsIn;

class SchoolSettingsMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    use SignsIn;

    public function test_get_redirected_without_school_settings()
    {
        $this->user->assign('school admin');
        $this->school->update(['currency_id' => null]);

        $this->get(route('students.index'))
            ->assertSessionHas('error')
            ->assertRedirect(route('settings.school'));
    }
}
