<?php

namespace App\Models;

use App\Jobs\SyncSchool;
use App\Notifications\TenantSyncComplete;
use App\Notifications\TenantSyncFailed;
use App\SisProviders\SisProvider;
use GrantHolle\Http\Resources\Traits\HasResource;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Spatie\Multitenancy\Models\Tenant as TenantBase;

/**
 * App\Models\Tenant
 *
 * @mixin IdeHelperTenant
 * @property int $id
 * @property string $name
 * @property string $domain
 * @property string|null $ps_url
 * @property string|null $ps_client_id
 * @property string|null $ps_secret
 * @property bool $allow_password_auth
 * @property string|null $subscription_started_at
 * @property string|null $subscription_expires_at
 * @property string $license
 * @property string $sis_provider
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $sync_notification_emails
 * @property bool $allow_oidc_login
 * @property string|null $smtp_host
 * @property string|null $smtp_port
 * @property string|null $smtp_username
 * @property string|null $smtp_password
 * @property string|null $smtp_from_name
 * @property string|null $smtp_from_address
 * @property string|null $smtp_encryption
 * @property string|null $batch_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Department[] $departments
 * @property-read int|null $departments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeeCategory[] $feeCategories
 * @property-read int|null $fee_categories_count
 * @property-read string $sis
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\School[] $schools
 * @property-read int|null $schools_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Section[] $sections
 * @property-read int|null $sections_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Student[] $students
 * @property-read int|null $students_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SyncTime[] $syncTimes
 * @property-read int|null $sync_times_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Spatie\Multitenancy\TenantCollection|static[] all($columns = ['*'])
 * @method static \Database\Factories\TenantFactory factory(...$parameters)
 * @method static \Spatie\Multitenancy\TenantCollection|static[] get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereAllowOidcLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereAllowPasswordAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant wherePsClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant wherePsSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant wherePsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSisProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpEncryption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpFromAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpFromName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSmtpUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSubscriptionExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSubscriptionStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereSyncNotificationEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant whereUpdatedAt($value)
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
        return $this->hasMany(SyncTime::class)->orderBy('hour');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function feeCategories(): HasMany
    {
        return $this->hasMany(FeeCategory::class);
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

    public function notifySyncEmails(string $notification)
    {
        collect($this->getSyncNotificationEmails())
            ->each(function ($email) use ($notification) {
                ray("Notifying {$email}", $notification);

                Notification::route('mail', $email)
                    ->notify(new $notification());
            });
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

    /**
     * This syncs all the schools' basic info
     * Then syncs only the active schools' SIS data
     * terms, students, teachers, courses, sections, and enrollment
     *
     * @return $this
     * @throws \Throwable
     */
    public function startSisSync(): static
    {
        ray()->clearScreen();
        ray('Starting sis sync');

        $batch = Bus::batch(
            $this->sisProvider()
                ->syncSchools()
                ->filter(fn (School $school) => $school->active)
                ->map(fn (School $school) => new SyncSchool($school))
        )->then(function (Batch $batch) {
            $this->notifySyncEmails(TenantSyncComplete::class);
        })->catch(function (Batch $batch, \Throwable $ex) {
            $this->notifySyncEmails(TenantSyncFailed::class);
        })->finally(function (Batch $batch) {
            ray("Batch {$batch->id} has finished");
            $this->refresh()->update(['batch_id' => null]);
        })->name('Tenant SIS Sync')->dispatch();

        $this->update(['batch_id' => $batch->id]);
        ray("Batch {$batch->id} has been dispatched");

        return $this;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'license' => $this->license,
            'ps_url' => $this->ps_url,
            'ps_client_id' => $this->ps_client_id,
            'ps_secret' => $this->ps_secret,
            'allow_password_auth' => $this->allow_password_auth,
            'allow_oidc_login' => $this->allow_oidc_login,
            'sync_notification_emails' => $this->sync_notification_emails,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_username' => $this->smtp_username,
            'smtp_password' => $this->smtp_password,
            'smtp_from_name' => $this->smtp_from_name,
            'smtp_from_address' => $this->smtp_from_address,
            'smtp_encryption' => $this->smtp_encryption,
            'is_syncing' => !!$this->batch_id,
            'is_cloud' => config('app.cloud'),
        ];
    }
}
