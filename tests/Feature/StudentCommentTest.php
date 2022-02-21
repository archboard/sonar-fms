<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StudentCommentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected bool $signIn = true;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = $this->createStudent();
    }

    public function test_authorization()
    {
        $this->post(route('students.comments.store', $this->student))
            ->assertForbidden();
    }

    public function test_comment_validation()
    {
        $this->assignPermission('comment', Student::class);
        $this->post(route('students.comments.store', $this->student), ['comment' => null])
            ->assertSessionHasErrors('comment')
            ->assertRedirect();
    }

    public function test_can_comment_on_student()
    {
        $this->assignPermission('comment', Student::class);
        $data = [
            'comment' => $this->faker->paragraph(),
        ];

        $this->post(route('students.comments.store', $this->student), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $this->assertEquals(1, $this->student->comments()->count());
        $comment = $this->student->comments()->first();

        $this->assertEquals($data['comment'], $comment->comment);
    }
}
