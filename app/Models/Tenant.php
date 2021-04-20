<?php

namespace App\Models;

use App\SisProviders\SisProvider;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;
use Silber\Bouncer\BouncerFacade;
use Spatie\Multitenancy\Models\Tenant as TenantBase;

/**
 * @mixin IdeHelperTenant
 */
class Tenant extends TenantBase
{
    use HasFactory;
    use HasResource;

    protected $guarded = [];
    protected $casts = [
        'allow_password_auth' => 'boolean',
        'allow_oidc_login' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function (Tenant $tenant) {
            // Seed the roles and abilities for this tenant scope
            BouncerFacade::scope()->to($tenant->id);
            BouncerFacade::allow(User::DISTRICT_ADMIN)->everything();

            // Additional seeding as the project needs
        });
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function syncTimes(): HasMany
    {
        return $this->hasMany(SyncTime::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function getSisAttribute(): string
    {
        return $this->sisProvider()->getSisLabel();
    }

    public function getSyncNotificationEmails(): array
    {
        if (!$this->sync_notification_emails) {
            return [];
        }

        // Split by comma, semi-colon, pipe or space
        $emails = preg_split('/([,;| ])/', $this->sync_notification_emails);

        return array_reduce($emails, function ($emails, $email) {
            $email = trim($email);
            $validator = Validator::make(compact('email'), ['email' => 'required|email']);

            if ($validator->valid()) {
                $emails[] = $email;
            }

            return $emails;
        }, []);
    }

    public function sisProvider(): SisProvider
    {
        return new $this->sis_provider($this);
    }

    public function getSchoolFromSisId($sisId): School
    {
        if ($sisId instanceof School) {
            return $sisId;
        }

        /** @var School $school */
        $school = $this->schools()->where('sis_id', $sisId)->firstOrFail();
        return $school;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'ps_url' => $this->ps_url,
            'ps_client_id' => $this->ps_client_id,
            'ps_secret' => $this->ps_secret,
            'allow_password_auth' => $this->allow_password_auth,
        ];
    }
}
