<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MyStudentsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $signIn = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpContact();
    }

    public function test_can_view_my_students()
    {
        $this->get('/my-students')
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('title')
                ->where('students', fn ($res) => count($res) === $this->user->students()->count())
                ->component('my-students/Index')
            );
    }
}
