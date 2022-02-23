<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class StudentTagTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected Student $student;
    protected bool $signIn = true;
    protected Collection $tags;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = $this->createStudent();

        // Seed 2 tags
        $this->tags = Collection::times(2)
            ->map(fn () => Tag::findOrCreate($this->faker->word(), Tag::student($this->school)));
    }

    public function test_can_fetch_tags()
    {
        $json = $this->get('/tags/students')
            ->assertOk()
            ->json();

        foreach ($json as $item) {
            $this->assertTrue($this->tags->some(fn ($tag) => $tag->name === $item));
        }
    }

    public function test_cant_fetch_tags_for_student_without_permission()
    {
        $this->get(route('students.tags.index', $this->student))
            ->assertForbidden();
    }

    public function test_can_fetch_tags_for_student()
    {
        $this->assignPermission('viewAny', Student::class);

        $tag = $this->tags->first()->name;
        $this->student->attachTag($tag, Tag::student($this->school));

        $json = $this->get(route('students.tags.index', $this->student))
            ->assertOk()
            ->json();

        $this->assertCount(1, $json);
        $this->assertEquals($tag, $json[0]);
    }
}
