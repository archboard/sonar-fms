<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FamiliesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected bool $signIn = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assignPermission('update', Student::class);
        $this->assignPermission('view', Student::class);
    }

    public function test_can_create_new_family()
    {
        $data = [
            'family_id' => null,
            'students' => $this->school->students()->limit(2)->pluck('uuid')->toArray(),
            'name' => $this->faker->word(),
            'notes' => null,
        ];

        $this->post('/families', $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(1, $this->school->families()->count());
        $family = $this->school->families()->first();

        $this->assertEquals($data['name'], $family->name);
        $this->assertEquals($data['notes'], $family->notes);
        $this->assertTrue(
            Student::whereIn('uuid', $data['students'])
                ->where('family_id', $family->id)
                ->exists()
        );
    }

    public function test_can_add_students_to_new_family()
    {
        /** @var Family $family */
        $family = Family::factory()->create();
        $this->assertEquals(1, $this->school->families()->count());

        $data = [
            'family_id' => $family->id,
            'students' => $this->school->students()->limit(2)->pluck('uuid')->toArray(),
            'name' => $this->faker->word(),
            'notes' => null,
        ];

        $this->post('/families', $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(1, $this->school->families()->count());
        $this->assertNotEquals($data['name'], $family->name);
        $this->assertNotEquals($data['notes'], $family->notes);
        $this->assertTrue(
            Student::whereIn('uuid', $data['students'])
                ->where('family_id', $family->id)
                ->exists()
        );
    }

    public function test_can_get_family()
    {
        /** @var Family $family */
        $family = Family::factory()->create();

        $this->get("/families/{$family->id}")
            ->assertJsonStructure([
                'name',
                'students',
            ]);
    }

    public function test_can_add_student_to_existing_family()
    {
        /** @var Family $family */
        $family = Family::factory()->create();
        /** @var Student $student */
        $student = $this->school->students->random();

        $this->assertNull($student->family_id);

        $this->post("/families/{$family->id}/students/{$student->uuid}")
            ->assertJsonStructure(['level', 'message']);

        $student->refresh();
        $this->assertEquals($family->id, $student->family_id);
    }

    public function test_can_remove_student_from_existing_family()
    {
        /** @var Family $family */
        $family = Family::factory()->create();
        /** @var Student $student */
        $student = $this->school->students->random();
        $student->update(['family_id' => $family->id]);

        $this->assertEquals($family->id, $student->family_id);

        $this->delete("/families/{$family->id}/students/{$student->uuid}")
            ->assertJsonStructure(['level', 'message']);

        $student->refresh();
        $this->assertNull($student->family_id);
    }
}
