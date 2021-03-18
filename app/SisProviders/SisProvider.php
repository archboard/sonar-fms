<?php

namespace App\SisProviders;

use App\Models\School;
use App\Models\Tenant;
use GrantHolle\PowerSchool\Api\RequestBuilder;
use Illuminate\Support\Collection;

interface SisProvider
{
    public function __construct(Tenant $tenant);
    public function getSisLabel(): string;
    public function getAllSchools(): array;
    public function syncSchools(): Collection;
    public function getSchool($sisId);
    public function syncSchool($sisId): School;
    public function syncSchoolStaff($sisId);
    public function syncSchoolStudents($sisId);
    public function syncSchoolCourses($sisId);
    public function syncSchoolSections($sisId);
    public function syncSchoolStudentEnrollment($sisId);
    public function syncSchoolTerms($sisId);
    public function fullSchoolSync($sisId);
    public function getBuilder(): RequestBuilder;
}
