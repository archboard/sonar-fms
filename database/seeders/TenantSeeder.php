<?php

namespace Database\Seeders;

use App\Models\InvoiceLayout;
use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\Term;
use Illuminate\Database\Seeder;

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
        $tenant = Tenant::factory()->testing()->make();
        Tenant::where('domain', $tenant->domain)->delete();
        $tenant->save();

        $tenant->schools->each(function (School $school) use ($tenant) {
            $school->terms()->save(Term::factory()->make(['tenant_id' => $tenant->id]));
            $school->invoiceLayouts()->save(InvoiceLayout::factory()->make(['tenant_id' => $tenant->id]));

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
