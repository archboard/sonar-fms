<?php

namespace Tests;

use App\Factories\UuidFactory;
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

        $this->school = $this->tenant->schools->random();

        \Bouncer::scope()->to($this->school->id);
        \Bouncer::allow('school admin')->everything();
        \Bouncer::refresh();

        $this->app->bind(School::class, fn () => $this->school);
        $this->app->bind(Tenant::class, fn () => $this->tenant);
    }

    protected function uuid(): string
    {
        return UuidFactory::make();
    }

    public function createUser(): User
    {
        /** @var User $user */
        $user = $this->tenant->users()
            ->save(
                User::factory()->make([
                    'school_id' => $this->school->id,
                ])
            );

        $user->schools()->attach($this->school->id);

        return $user;
    }

    public function signIn(bool $force = false): User
    {
        if ($this->user && !$force) {
            return $this->user;
        }

        $user = $this->createUser();

        $this->be($user);
        $this->user = $user;

        return $user;
    }

    public function assignPermission($permission = '*', $model = '*'): User
    {
        return $this->user->givePermissionForSchool($this->user->school, $permission, $model);
    }

    public function manageTenancy(): User
    {
        $this->user->update(['manages_tenancy' => true]);

        return $this->user;
    }

    public function addUser(): User
    {
        return $this->createUser();
    }
}
