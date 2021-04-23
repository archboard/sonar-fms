<?php

namespace App\SisProviders;

use App\Events\SchoolSyncComplete;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use GrantHolle\PowerSchool\Api\Facades\PowerSchool;
use GrantHolle\PowerSchool\Api\RequestBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function getSchoolsFromSis(): array
    {
        $response = $this->builder
            ->to('/ws/v1/district/school')
            ->get();

        return array_map(function ($school) {
            return new School([
                'sis_id' => $school->id,
                'name' => $school->name,
                'school_number' => $school->school_number,
                'low_grade' => $school->low_grade,
                'high_grade' => $school->high_grade,
            ]);
        }, $response->schools->school);
    }

    public function getAllSchools(): array
    {
        return $this->getSchoolsFromSis();
    }

    public function syncSchools(): Collection
    {
        return collect($this->getAllSchools())
            ->map(function (School $school) {
                return $this->tenant
                    ->schools()
                    ->updateOrCreate(
                        ['sis_id' => $school->sis_id],
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
        if ($sisId instanceof School) {
            $sisId = $sisId->sis_id;
        }

        $results = $this->builder
            ->to("/ws/v1/school/{$sisId}")
            ->get();

        if (
            config('app.cloud') &&
            !$this->tenant->schools()->where('sis_id', $sisId)->exists()
        ) {
            throw new \Exception("Your license does not support this school. Please update your license and try again.");
        }

        return $results->school;
    }

    public function syncSchool($sisId): School
    {
        $sisSchool = $this->getSchool($sisId);

        /** @var School $school */
        $school = $this->tenant
            ->schools()
            ->updateOrCreate(
                ['sis_id' => $sisSchool->id],
                [
                    'name' => $sisSchool->name,
                    'school_number' => $sisSchool->school_number,
                    'low_grade' => $sisSchool->low_grade,
                    'high_grade' => $sisSchool->high_grade,
                ]
            );

        return $school;
    }

    public function syncSchoolStaff($sisId)
    {
        $school = $this->tenant->getSchoolFromSisId($sisId);
        $builder = $this->builder
            ->method('get')
            ->to("/ws/v1/school/{$school->sis_id}/staff")
            ->expansions('emails');

        while ($results = $builder->paginate()) {
            $existingUsers = $this->tenant
                ->users()
                ->whereIn('sis_id', collect($results)->pluck('users_dcid'))
                ->with('schools')
                ->get()
                ->keyBy('sis_id');

            $entries = array_reduce(
                $results,
                function ($entries, $user) use ($school, $existingUsers) {
                    $email = strtolower(optional($user->emails)->work_email);

                    if (!$email) {
                        return $entries;
                    }

                    /** @var User $existingUser */
                    if ($existingUser = $existingUsers->get($user->users_dcid)) {
                        $existingUser->update([
                            'email' => $email,
                            'first_name' => optional($user->name)->first_name,
                            'last_name' => optional($user->name)->last_name,
                        ]);
                        /** @var School $existingSchool */
                        $existingSchool = $existingUser->schools->firstWhere('id', $school->id);

                        // If the school record exists already, update the staff id just in case
                        if ($existingSchool && $existingSchool->pivot->staff_id !== $user->id) {
                            $existingUser->schools()
                                ->updateExistingPivot($school->id, ['staff_id' => $user->id]);
                        }
                        // If there isn't a school relationship, add it here
                        elseif (!$existingSchool) {
                            $entries[] = [
                                'school_id' => $school->id,
                                'user_id' => $existingUser->id,
                                'staff_id' => $user->id,
                            ];
                        }

                        return $entries;
                    }

                    /** @var User $newUser */
                    $newUser = $this->tenant
                        ->users()
                        ->create([
                            'sis_id' => $user->users_dcid,
                            'email' => $email,
                            'first_name' => optional($user->name)->first_name,
                            'last_name' => optional($user->name)->last_name,
                            'school_id' => $school->id,
                        ]);

                    $entries[] = [
                        'school_id' => $school->id,
                        'user_id' => $newUser->id,
                        'staff_id' => $user->id,
                    ];

                    return $entries;
                }
            );

            if (!empty($entries)) {
                DB::table('school_user')->insert($entries);
            }
        }
    }

    public function syncSchoolStudents($sisId)
    {
        $school = $this->tenant->getSchoolFromSisId($sisId);
        $builder = $this->builder
            ->method('get')
            ->to("/ws/v1/school/{$school->sis_id}/student")
            ->q('school_enrollment.enroll_status==(A,P,G,T,H,I)')
            ->expansions('contact_info,school_enrollment,initial_enrollment');

        while ($results = $builder->paginate()) {
            $now = now()->format('Y-m-d H:i:s');

            // Get courses that exist already
            $existingStudents = $school->students()
                ->whereIn('sis_id', collect($results)->pluck('id'))
                ->get()
                ->keyBy('sis_id');

            $entries = array_reduce(
                $results,
                function ($entries, $student) use ($school, $now, $existingStudents) {
                    $attributes = $this->getStudentAttributes($student);

                    // If it exists, then update
                    if ($existingStudent = $existingStudents->get($student->id)) {
                        $existingStudent->update($attributes);
                        return $entries;
                    }

                    // It's a new student
                    $attributes['tenant_id'] = $this->tenant->id;
                    $attributes['school_id'] = $school->id;
                    $attributes['sis_id'] = $student->id;
                    $attributes['created_at'] = $now;
                    $attributes['updated_at'] = $now;
                    $entries[] = $attributes;

                    return $entries;
                }, []
            );

            if (!empty($entries)) {
                DB::table('students')->insert($entries);
            }
        }
    }

    public function syncStudent($sisId)
    {
        $student = $sisId instanceof Student
            ? $sisId
            : Student::where('sis_id', $sisId)->first();

        $id = $student
            ? $student->sis_id
            : $sisId;
        $response = $this->builder->to("/ws/v1/student/{$id}")
            ->expansions('contact_info,school_enrollment,initial_enrollment')
            ->get();

        $attributes = $this->getStudentAttributes($response->student);

        if ($student) {
            $student->update($attributes);
            return;
        }

        $school = School::where('sis_id', $response->school_enrollment->school_id)
            ->first();

        if (!$school) {
            return;
        }

        $now = now();
        $attributes['school_id'] = $school->id;
        $attributes['sis_id'] = $student->id;
        $attributes['created_at'] = $now;
        $attributes['updated_at'] = $now;

        $this->tenant->students()->create($attributes);
    }

    protected function getStudentAttributes($student)
    {
        $email = strtolower(optional($student->contact_info)->email);

        return [
            'student_number' => $student->local_id,
            'first_name' => optional($student->name)->first_name,
            'last_name' => optional($student->name)->last_name,
            'email' => $email ?: null,
            'grade_level' => $student->school_enrollment->grade_level,
            'enrolled' => $student->school_enrollment->enroll_status_code === 0,
            'enroll_status' => $student->school_enrollment->enroll_status_code,
            'current_entry_date' => optional($student->school_enrollment)->entry_date,
            'current_exit_date' => optional($student->school_enrollment)->exit_date,
            'initial_district_entry_date' => optional($student->initial_enrollment)->district_entry_date,
            'initial_school_entry_date' => optional($student->initial_enrollment)->school_entry_date,
            'initial_district_grade_level' => $student->initial_enrollment->district_entry_grade_level,
            'initial_school_grade_level' => $student->initial_enrollment->school_entry_grade_level,
        ];
    }

    public function syncSchoolCourses($sisId)
    {
        $school = $this->tenant->getSchoolFromSisId($sisId);
        $builder = $this->builder
            ->method('get')
            ->to("/ws/v1/school/{$school->sis_id}/course");

        try {
            while ($results = $builder->paginate()) {
                $now = now()->format('Y-m-d H:i:s');

                // Get courses that exist already
                $existingCourses = $school->courses()
                    ->whereIn('sis_id', collect($results)->pluck('id'))
                    ->get()
                    ->keyBy('sis_id');

                $entries = array_reduce(
                    $results,
                    function ($entries, $course) use ($school, $now, $existingCourses) {
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
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        return $entries;
                    }, []
                );

                if (!empty($entries)) {
                    DB::table('courses')->insert($entries);
                }
            }
        } catch (\Exception $exception) {
            Log::error("Failed importing courses for {$school->name}.", ['exception' => $exception]);
        }
    }

    public function syncSchoolSections($sisId)
    {
        $school = $this->tenant->getSchoolFromSisId($sisId);
        $builder = $this->builder
            ->method('get')
            ->to("/ws/v1/school/{$school->sis_id}/section")
            ->expansions('term');
        $activeSections = [];
        $newEntries = [];

        while ($results = $builder->paginate()) {
            $now = now()->format('Y-m-d H:i:s');

            // Get courses that exist already
            $courses = $school->courses()
                ->whereIn('sis_id', collect($results)->pluck('course_id'))
                ->get()
                ->keyBy('sis_id');
            $existingSections = $school->sections()
                ->whereIn('sis_id', collect($results)->pluck('id'))
                ->get()
                ->keyBy('sis_id');
            $staff = $school->users()
                ->wherePivotIn('staff_id', collect($results)->pluck('staff_id'))
                ->get()
                ->keyBy('pivot.staff_id');
            $terms = $school->terms()
                ->where('start_year', $results[0]->term->start_year)
                ->get()
                ->keyBy('sis_id');

            $entries = array_reduce(
                $results,
                function ($entries, $section) use (&$activeSections, $school, $terms, $staff, $courses, $now, $existingSections) {
                    $course = $courses->get($section->course_id);
                    $teacher = $staff->get($section->staff_id);
                    $term = $terms->get($section->term->id);

                    // If the course or staff doesn't exists, don't do anything
                    if (!$course || !$teacher) {
                        return $entries;
                    }

                    // If the section exists, then update
                    if ($existingSection = $existingSections->get($section->id)) {
                        $activeSections[] = $existingSection->id;
                        $existingSection->update([
                            'term_id' => $term->id,
                            'section_number' => optional($section)->section_number,
                            'expression' => optional($section)->expression,
                            'external_expression' => optional($section)->external_expression,
                        ]);

                        return $entries;
                    }

                    // It's a new section
                    $entries[] = [
                        'tenant_id' => $this->tenant->id,
                        'school_id' => $school->id,
                        'term_id' => $term->id,
                        'course_id' => $course->id,
                        'user_id' => $teacher->id,
                        'sis_id' => $section->id,
                        'section_number' => optional($section)->section_number,
                        'expression' => optional($section)->expression,
                        'external_expression' => optional($section)->external_expression,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // At this point they are a teacher
                    $teacher->assign(User::TEACHER);

                    return $entries;
                }, []);

            $newEntries = array_merge($newEntries, $entries);
        }

        // Delete the existing sections that didn't update
        // which means the other existing sections aren't active
        $school->sections()
            ->whereNotIn('id', $activeSections)
            ->delete();

        if (!empty($newEntries)) {
            DB::table('sections')->insert($newEntries);
        }
    }

    public function syncSchoolStudentEnrollment($sisId)
    {
        $school = $this->tenant->getSchoolFromSisId($sisId);
        $students = $this->tenant->students()
            ->select(['id', 'sis_id'])
            ->get()
            ->keyBy('sis_id');

        $sectionStudents = $school->sections()
            ->get()
            ->reduce(function (array $sectionStudents, Section $section) use ($students) {
                $results = $this->builder
                    ->extensions('s_cc_x,s_cc_edfi_x')
                    ->to("/ws/v1/section/{$section->sis_id}/section_enrollment")
                    ->get();

                $enrollments = $results->section_enrollments->section_enrollment ?? [];

                if (!is_array($enrollments)) {
                    $enrollments = [$enrollments];
                }

                $sectionEnrollment = collect($enrollments)
                    ->reduce(function (array $enrollments, $enrollment) use ($section, $students) {
                        if (!is_object($enrollment)) {
                            return $enrollments;
                        }

                        $student = $students->get($enrollment->student_id);

                        if (!$student || $enrollment->dropped) {
                            return $enrollments;
                        }

                        $enrollments[] = [
                            'section_id' => $section->id,
                            'student_id' => $student->id,
                        ];

                        return $enrollments;
                    }, []);

                return array_merge($sectionStudents, $sectionEnrollment);
            }, []);

        DB::transaction(function () use ($sectionStudents) {
            $table = DB::table('section_student');
            $sectionIds = array_unique(Arr::pluck($sectionStudents, 'section_id'));

            $table->whereIn('section_id', $sectionIds)->delete();
            $table->insert($sectionStudents);
        });
    }

    public function syncSchoolTerms($sisId)
    {
        $school = $this->tenant->getSchoolFromSisId($sisId);

        $results = $this->builder
            ->to("/ws/v1/school/{$school->sis_id}/term")
            ->get();

        $terms = is_array($results->terms->term)
            ? $results->terms->term
            : [$results->terms->term];

        collect($terms)->each(function ($term) use ($school) {
            $school->terms()
                ->updateOrCreate(
                    [
                        'tenant_id' => $this->tenant->id,
                        'school_id' => $school->id,
                        'sis_id' => $term->id,
                    ],
                    [
                        'sis_assigned_id' => $term->local_id,
                        'name' => $term->name,
                        'abbreviation' => $term->abbreviation,
                        'start_year' => $term->start_year,
                        'portion' => $term->portion,
                        'starts_at' => $term->start_date,
                        'ends_at' => $term->end_date,
                    ]
                );
        });
    }

    public function syncSchoolStudentGuardians($sisId)
    {
        $school = $this->tenant->getSchoolFromSisId($sisId);
        $schools = School::select(['id', 'school_number'])
            ->get()
            ->keyBy('school_number');

        $school->students()
            ->get()
            ->each(
                fn (Student $student) => $student->syncGuardians($schools)
            );
    }

    /**
     * Syncs everything for a school:
     * staff, students, courses, sections, and enrollment
     *
     * @param int|string $sisId
     */
    public function fullSchoolSync($sisId)
    {
        $school = $this->tenant->getSchoolFromSisId($sisId);
        \Bouncer::cache();

        ray()->newScreen("Sync for {$school->name}");
        ray($school->name, 'syncing school info');
        $this->syncSchool($sisId);
        ray($school->name, 'syncing school terms');
        $this->syncSchoolTerms($sisId);
        ray($school->name, 'syncing school staff');
        $this->syncSchoolStaff($sisId);
        ray($school->name, 'syncing school students');
        $this->syncSchoolStudents($sisId);
        ray($school->name, 'syncing school guardians');
        $this->syncSchoolStudentGuardians($sisId);
        ray($school->name, 'syncing school courses');
        $this->syncSchoolCourses($sisId);
        ray($school->name, 'syncing school sections');
        $this->syncSchoolSections($sisId);
        ray($school->name, 'syncing school enrollment');
        $this->syncSchoolStudentEnrollment($sisId);

        \Bouncer::refresh();
        event(new SchoolSyncComplete($school));
        ray()->clearScreen();
    }

    public function getBuilder(): RequestBuilder
    {
        return $this->builder;
    }

    public function getSisLabel(): string
    {
        return 'PowerSchool';
    }
}
