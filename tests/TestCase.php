<?php

namespace Tests;

use App\Factories\UuidFactory;
use App\Models\InvoiceLayout;
use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Collection;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected Tenant $tenant;
    protected School $school;
    protected ?User $user = null;
    protected bool $signIn = false;

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
//        \Bouncer::dontCache();

        $this->app->bind(School::class, fn () => $this->school);
        $this->app->bind(Tenant::class, fn () => $this->tenant);

        if ($this->signIn) {
            $this->signIn();
        }
    }

    protected function uuid(): string
    {
        return UuidFactory::make();
    }

    public function createUser(array $attributes = []): User
    {
        $defaultAttributes = ['school_id' => $this->school->id];

        /** @var User $user */
        $user = $this->tenant->users()
            ->save(
                User::factory()->make(
                    array_merge($defaultAttributes, $attributes)
                )
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

    public function setUpContact(int $students = 2): Collection
    {
        $this->user->assign('contact');

        return Collection::times($students)
            ->map(fn () => $this->createStudent())
            ->each(function (Student $student) {
                $this->user->allow('view', $student);
                $this->user->students()->attach($student);
            });
    }

    public function createStudent(array $attributes = []): Student
    {
        $attributes = array_merge(
            ['tenant_id' => $this->tenant->id],
            $attributes
        );

        /** @var Student $student */
        $student = $this->school->students()
            ->save(Student::factory()->make($attributes));

        return $student;
    }

    public function createStudents(array $attributes, int $count): Collection
    {
        $attributes = array_merge(
            ['tenant_id' => $this->tenant->id],
            $attributes
        );

        return $this->school->students()
            ->saveMany(
                Student::factory()
                    ->count($count)
                    ->make($attributes)
            );
    }

    protected function dateFromDatePicker(Carbon $date): string
    {
        return $date->format('Y-m-d\TH:i:s.000\Z');
    }
}
