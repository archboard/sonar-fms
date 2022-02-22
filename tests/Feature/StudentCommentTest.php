<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Student;
use App\Models\User;
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

    protected function createComment(User $user = null): Comment
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->student
            ->commentAsUser($user ?? $this->user, $this->faker->paragraph());
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
//            ->assertJsonStructure(['level', 'message', 'data']);

        $this->assertEquals(1, $this->student->comments()->count());
        $comment = $this->student->comments()->first();

        $this->assertEquals($data['comment'], $comment->comment);
    }

    public function test_can_edit_a_comment()
    {
        $this->assignPermission('comment', Student::class);
        $comment = $this->createComment();
        $data = [
            'comment' => $this->faker->paragraphs(asText: true),
        ];

        $this->put(route('students.comments.update', [$this->student, $comment]), $data)
            ->assertSessionHas('success')
            ->assertRedirect();

        $comment->refresh();
        $this->assertEquals($data['comment'], $comment->comment);
    }

    public function test_cant_edit_comment_by_someone_else()
    {
        $this->assignPermission('comment', Student::class);
        $user = $this->createUser();
        $comment = $this->createComment($user);
        $data = [
            'comment' => $this->faker->paragraphs(asText: true),
        ];

        $this->put(route('students.comments.update', [$this->student, $comment]), $data)
            ->assertForbidden();
    }
}
