<?php

namespace Tests;

use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Do a completely fresh db
        $this->artisan('migrate:refresh --force --seed');

        $this->tenant = Tenant::firstOrFail();
        $this->tenant->load('schools');
        $this->tenant->makeCurrent();

        \Bouncer::refresh();
    }

    public function signIn(): User
    {
        /** @var School $school */
        $school = $this->tenant->schools->random();

        /** @var User $user */
        $user = $this->tenant->users()
            ->save(
                User::factory()->make([
                    'school_id' => $school->id,
                ])
            );

        $user->schools()->attach($school->id);

        return $user;
    }
}
