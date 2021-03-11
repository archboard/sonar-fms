<?php

namespace App\Models;

use App\SisProviders\SisProvider;
use GrantHolle\Http\Resources\Traits\HasResource;
use GrantHolle\PowerSchool\Api\Facades\PowerSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Tenant as TenantBase;

/**
 * @mixin IdeHelperTenant
 */
class Tenant extends TenantBase
{
    use HasFactory;
    use HasResource;

    protected $guarded = [];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sisProvider(): SisProvider
    {
        return new $this->sis_provider($this);
    }

    public function getSchoolsFromSis(): array
    {
        $response = PowerSchool::to('/ws/v1/district/school')->get();

        return $response->schools->school;
    }

    public function syncAllSchoolsFromSis()
    {

    }
}
