<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    public function test_cant_scholarships_without_permission()
    {
        $this->get(route('scholarships.index'))
            ->assertForbidden();
    }

    public function test_can_see_all_scholarships()
    {
        $this->assignPermission('viewAny', Scholarship::class);

        $this->get(route('scholarships.index'))
            ->assertOk();
    }

    public function test_can_create_new_scholarship()
    {
        $this->assignPermission('create', Scholarship::class);
        $data = [
            'name' => 'Tuition Assistance',
            'description' => 'This is a test scholarship',
            'percentage' => '70.99',
            'amount' => 20000,
        ];

        $this->post(route('scholarships.store'), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $this->assertDatabaseHas('scholarships', $data);

        $this->assertEquals(1, $this->school->scholarships()->count());
    }

    public function test_needs_an_amount_or_percentage()
    {
        $this->assignPermission('create', Scholarship::class);
        $data = [
            'name' => 'Tuition Assistance',
            'description' => 'This is a test scholarship',
            'percentage' => null,
            'amount' => null,
        ];

        $this->post(route('scholarships.store'), $data)
            ->assertRedirect()
            ->assertSessionHasErrors([
                'percentage', 'amount',
            ]);
    }

    public function test_percentage_has_to_be_less_than_100()
    {
        $this->assignPermission('create', Scholarship::class);
        $data = [
            'name' => 'Tuition Assistance',
            'description' => 'This is a test scholarship',
            'percentage' => 101.77,
            'amount' => null,
        ];

        $this->post(route('scholarships.store'), $data)
            ->assertRedirect()
            ->assertSessionHasErrors([
                'percentage',
            ]);
    }

    public function test_can_update_existing_scholarship()
    {
        $this->assignPermission('update', Scholarship::class);
        $scholarship = $this->school->scholarships()
            ->save(
                Scholarship::factory()->make(['tenant_id' => $this->tenant->id])
            );
        $data = [
            'name' => 'Tuition Assistance',
            'description' => 'This is a test scholarship',
            'percentage' => '70.99',
            'amount' => 20000,
        ];

        $this->put(route('scholarships.update', $scholarship), $data)
            ->assertRedirect()
            ->assertSessionHas('success');

        $data['tenant_id'] = $this->tenant->id;
        $data['school_id'] = $this->school->id;
        $this->assertDatabaseHas('scholarships', $data);
        $this->assertEquals(1, $this->school->scholarships()->count());
    }

    public function test_can_delete_scholarship()
    {
        $this->assignPermission('delete', Scholarship::class);
        $scholarship = $this->school->scholarships()
            ->save(
                Scholarship::factory()->make(['tenant_id' => $this->tenant->id])
            );

        $this->delete(route('scholarships.destroy', $scholarship))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(0, $this->school->scholarships()->count());
    }
}
