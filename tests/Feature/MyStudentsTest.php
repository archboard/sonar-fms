<?php

namespace Tests\Feature;

use App\Models\Student;
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

    protected function getContactStudent(): Student
    {
        return $this->user->students->random();
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

    public function test_cant_view_a_non_my_student()
    {
        $student = $this->createStudent();

        $this->get(route('my-students.show', $student))
            ->assertForbidden();
    }

    public function test_can_view_my_student()
    {
        $this->get(route('my-students.show', $this->getContactStudent()))
            ->assertOk()
            ->assertViewHas('title')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('title')
                ->has('student')
                ->component('my-students/Show')
            );
    }

    public function test_cant_retrieve_student_invoices_without_permission()
    {
        $student = $this->createStudent();

        $this->get("/students/{$student->uuid}/invoices")
            ->assertForbidden();
    }

    public function test_can_retrieve_student_invoices()
    {
        $student = $this->getContactStudent();

        $this->get("/students/{$student->uuid}/invoices")
            ->assertOk();
    }
}
