<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->testing()->create();

        /** @var Collection $schools */
        $schools = $tenant->schools()
            ->saveMany(School::factory()->count(3)->make());

        $schools->each(function (School $school) use ($tenant) {
            // Create students for the school
            $school->students()->saveMany(
                Student::factory()->count(5)->make(['tenant_id' => $tenant->id])
            );
            $school->students()->saveMany(
                Student::factory()->unenrolled()->count(5)->make(['tenant_id' => $tenant->id])
            );
        });
    }
}
