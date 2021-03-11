<?php

namespace App\SisProviders;

use App\Models\School;
use App\Models\Tenant;
use GrantHolle\PowerSchool\Api\RequestBuilder;
use Illuminate\Support\Collection;

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

    public function getBuilder()
    {
        return $this->builder;
    }
}
