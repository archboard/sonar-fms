<?php

namespace App\SisProviders;

use App\Models\School;
use App\Models\Tenant;
use GrantHolle\PowerSchool\Api\RequestBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PowerSchoolProvider implements SisProvider
{
    protected Tenant $tenant;
    protected RequestBuilder $builder;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->builder = new RequestBuilder(
            $tenant->ps_url,
            $tenant->ps_client_id,
            $tenant->ps_secret
        );
    }

    public function getAllSchools(): array
    {
        $response = $this->builder
            ->to('/ws/v1/district/school')
            ->get();

        // Only get the schools that exist already
        if (config('app.cloud')) {
            $schools = $this->tenant->schools->pluck('dcid');
            return array_filter($response->schools->school, function ($school) use ($schools) {
                return $schools->contains($school->id);
            });
        }

        return $response->schools->school;
    }

    public function syncSchools(): Collection
    {
        return collect($this->getAllSchools())
            ->map(function ($school) {
                return $this->tenant
                    ->schools()
                    ->updateOrCreate(
                        ['dcid' => $school->id],
                        [
                            'name' => $school->name,
                            'school_number' => $school->school_number,
                            'low_grade' => $school->low_grade,
                            'high_grade' => $school->high_grade,
                        ]
                    );
            });
    }

    public function getSchool($sisId)
    {
        // TODO: Implement getSchool() method.
    }

    public function syncSchool($sisId): School
    {
        // TODO: Implement syncSchool() method.
    }

    public function syncSchoolCourses($sisId)
    {
        $builder = $this->builder
            ->method('get')
            ->to("/ws/v1/school/{$sisId}/course");
        $school = $sisId instanceof School
            ? $sisId
            : $this->tenant->schools()->firstWhere('dcid', $sisId);

        while ($results = $builder->paginate()) {
            $now = now();

            // Get courses that exist already
            $existingCourses = $school->courses()
                ->whereIn('sis_id', collect($results)->pluck('id'))
                ->get()
                ->keyBy('sis_id');

            $entries = array_reduce($results, function ($entries, $course) use ($school, $now, $existingCourses) {
                // If it exists, then update
                if ($existingCourse = $existingCourses->get($course->id)) {
                    $existingCourse->update([
                        'name' => $course->course_name,
                        'course_number' => $course->course_number,
                    ]);

                    return $entries;
                }

                // It's a new course
                $entries[] = [
                    'tenant_id' => $this->tenant->id,
                    'school_id' => $school->id,
                    'name' => $course->course_name,
                    'course_number' => $course->course_number,
                    'sis_id' => $course->id,
                    'created_at' => $now->format('Y-m-d H:i:s'),
                    'updated_at' => $now->format('Y-m-d H:i:s'),
                ];

                return $entries;
            }, []);

            if (!empty($entries)) {
                DB::table('courses')->insert($entries);
            }
        }
    }

    public function getBuilder(): RequestBuilder
    {
        return $this->builder;
    }
}
