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
    protected School $school;
    protected ?User $user = null;

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
        if ($this->user) {
            return $this->user;
        }

        $this->school = $this->tenant->schools->random();

        /** @var User $user */
        $user = $this->tenant->users()
            ->save(
                User::factory()->make([
                    'school_id' => $this->school->id,
                ])
            );

        $user->schools()->attach($this->school->id);
        \Bouncer::scope()->to($this->school->id);

        $this->be($user);
        $this->user = $user;

        return $user;
    }

    public function assignPermission($permission = '*', $model = '*'): User
    {
        return $this->user->givePermissionForSchool($this->user->school, $permission, $model);
    }
}
